<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Enums\PosSaleProcess;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessPosSalesJob;
use App\Jobs\ProcessSingleSaleJob;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Core\App\Models\CustomerModel;
use Modules\Core\App\Models\SettingTypeModel;
use Modules\Core\App\Models\UserModel;
use Modules\Domain\App\Models\DomainModel;
use Modules\Inventory\App\Http\Requests\PosSalesProcessRequest;
use Modules\Inventory\App\Models\ConfigModel;
use Modules\Inventory\App\Models\InvoiceItemTempModel;
use Modules\Inventory\App\Models\InvoiceTempModel;
use Modules\Inventory\App\Models\ParticularModel;
use Modules\Inventory\App\Models\PosSaleModel;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\SalesModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class PosController extends Controller
{
    protected $domain;

    public function __construct(Request $request)
    {
        $userId = $request->header('X-Api-User');
        if ($userId && !empty($userId)) {
            $userData = UserModel::getUserData($userId);
            $this->domain = $userData;
        }
    }

    public function checkInvoiceMode()
    {
        $config = ConfigModel::with('pos_invoice_mode')
            ->where('id', $this->domain['config_id'])
            ->first();

        if (!$config || !$config->pos_invoice_mode) {
            return response()->json([
                'status' => ResponseAlias::HTTP_NOT_FOUND,
                'message' => 'Config or invoice mode not found',
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        return match ($config->pos_invoice_mode->slug) {
            InvoiceTempModel::TABLE => $this->handleTableMode($this->domain['config_id']),
            InvoiceTempModel::CUSTOMER => $this->handleCustomerMode($this->domain['global_id']),
            InvoiceTempModel::USER => $this->handleUserMode($this->domain),
            default => response()->json([
                'status' => ResponseAlias::HTTP_BAD_REQUEST,
                'message' => 'Invalid invoice mode',
            ], ResponseAlias::HTTP_BAD_REQUEST),
        };
    }

    private function handleTableMode($config)
    {
        $tableDatas = ParticularModel::getEntityDropdown($config, 'table');
        if (empty($tableDatas)) {
            return response()->json([
                'status' => ResponseAlias::HTTP_NOT_FOUND,
                'message' => 'Table not found',
            ], ResponseAlias::HTTP_NOT_FOUND);
        }

        $tableIds = $tableDatas->pluck('id')->toArray();
        $tableExists = InvoiceTempModel::where('config_id', $this->domain['config_id'])
            ->where('invoice_mode', 'table')
            ->pluck('table_id')
            ->toArray();

        $toInsert = array_diff($tableIds, $tableExists);
        $toDelete = array_diff($tableExists, $tableIds);

        $this->deleteUnusedTables($toDelete);
        $this->insertNewTables($toInsert, $tableDatas);

        $getInvoiceTableData = InvoiceTempModel::getInvoiceTables($this->domain['config_id'],'table');

        if (!empty($getInvoiceTableData)) {
            return response()->json([
                'status' => ResponseAlias::HTTP_OK,
                'message' => 'success',
                'invoice_mode' => 'table',
                'data' => $getInvoiceTableData
            ], ResponseAlias::HTTP_OK);
        }

        return response()->json([
            'status' => ResponseAlias::HTTP_NOT_FOUND,
            'message' => 'Invoice table data not found',
        ], ResponseAlias::HTTP_NOT_FOUND);
    }
    private function deleteUnusedTables($toDelete)
    {
        if (!empty($toDelete)) {
            InvoiceTempModel::where('config_id', $this->domain['config_id'])
                ->whereIn('table_id', $toDelete)
                ->delete();
        }
    }
    private function insertNewTables($toInsert, $tableDatas)
    {
        if (!empty($toInsert)) {
            $records = [];
            foreach ($tableDatas as $table) {
                if (in_array($table->id, $toInsert)) {
                    $records[] = [
                        'config_id' => $this->domain['config_id'],
                        'created_by_id' => $this->domain['user_id'],
                        'table_id' => $table->id,
                        'is_active' => false,
                        'invoice_mode' => 'table',
                        'created_at' => now(),
                    ];
                }
            }
            InvoiceTempModel::insert($records);
        }
    }

    private function handleCustomerMode($config)
    {
        $this->createCustomerForSales($config);
        $getInvoiceCustomerData = InvoiceTempModel::getInvoiceTables($this->domain['config_id'],'customer');

        if (!empty($getInvoiceCustomerData)) {
            return response()->json([
                'status' => ResponseAlias::HTTP_OK,
                'message' => 'success',
                'invoice_mode' => 'customer',
                'data' => $getInvoiceCustomerData
            ], ResponseAlias::HTTP_OK);
        }

        return response()->json([
            'status' => ResponseAlias::HTTP_NOT_FOUND,
            'message' => 'Invoice table data not found',
        ], ResponseAlias::HTTP_NOT_FOUND);
    }

    private function createCustomerForSales($config)
    {
        $findDomain = DomainModel::find($config);
        // Ensure Default Customer Group Exists
        $defaultCustomerGroup = \Modules\Core\App\Models\SettingModel::firstOrCreate(
            [
                'domain_id' => $config,
                'slug' => 'default'
            ],
            [
                'name' => 'Default',
                'setting_type_id' => SettingTypeModel::where('slug', 'customer-group')->value('id'),
                'status' => 1
            ]
        );

        // Ensure Default Customer Exists
        $findDefaultCustomer = CustomerModel::firstOrCreate(
            [
                'domain_id' => $config,
                'customer_group_id' => $defaultCustomerGroup->id
            ],
            [
                'customer_unique_id' => "{$this->domain['global_id']}@default-customer-{$defaultCustomerGroup->id}",
                'name' => 'Default',
                'mobile' => $findDomain->mobile,
                'email' => 'default@default.com',
                'status' => true,
                'address' => 'Default',
                'slug' => Str::slug('Default'),
            ]
        );

        $customerExists = InvoiceTempModel::where('config_id', $this->domain['config_id'])->where('invoice_mode','customer')->first();
        if (empty($customerExists)) {
            $customerExists = InvoiceTempModel::create([
                'config_id' => $this->domain['config_id'],
                'created_by_id' => $this->domain['user_id'],
                'customer_id' => $findDefaultCustomer->id,
                'is_active' => false,
                'invoice_mode' => 'customer',
                'created_at' => now(),
            ]);
        }
        return $customerExists;
    }
    private function handleUserMode($config)
    {
        $data = UserModel::getRecordsForLocalStorage('',$config);
        // Return JSON Response
        return response()->json([
            'status' => ResponseAlias::HTTP_OK,
            'message' => 'success',
            'invoice_mode' => 'user',
            'data' => $data['entities']
        ], ResponseAlias::HTTP_OK);
    }

    public function invoiceUpdate(Request $request)
    {
        $input = $request->all();

        $allowedParticularFieldNames = ['table', 'customer', 'user'];
        if (in_array($input['field_name'], $allowedParticularFieldNames)) {
            try {
                // Deactivate all invoices first
                InvoiceTempModel::where('config_id', $this->domain['config_id'])
                    ->where('invoice_mode', $input['field_name'])
                    ->update(['is_active' => 0]);

                // Activate the specific invoice
                InvoiceTempModel::where('id', $input['invoice_id'])->update(['is_active' => 1]);

                return response()->json([
                    'status' => ResponseAlias::HTTP_OK,
                    'message' => 'success',
                ], ResponseAlias::HTTP_OK);

            } catch (\Exception $e) {
                Log::error('Error updating invoices: ' . $e->getMessage());
                return response()->json(['error' => 'An error occurred'], 500);
            }
        }

        $allowedParticularFieldNames = ['sales_by_id','amount','discount','customer_id','transaction_mode_id','discount_type'];
        if (in_array($input['field_name'], $allowedParticularFieldNames)) {
            try {
                $findInvoice = InvoiceTempModel::find($input['invoice_id'])
                    ?? InvoiceTempModel::where('config_id', $this->domain['config_id'])->where('is_active', 1)->first();

                if ($input['field_name'] == 'sales_by_id') {
                    $findInvoice->update([
                        $input['field_name'] => $input['value']
                    ]);
                }
                if ($input['field_name'] == 'amount') {
                    $findInvoice->update([
                        'payment' => $input['value']
                    ]);
                }

                if ($input['field_name'] == 'discount_type') {
                    $discountAmount = 0;
                    if ($input['value'] === "Flat") {
                        $discountAmount = $input['discount_amount'];
                    } else if ($input['value'] === "Percent") {
                        $discountAmount = ($findInvoice->sub_total * $input['discount_amount']) / 100;
                    }

                    $findInvoice->update([
                        'discount' => $discountAmount,
                        'percentage' => $input['value'] === "Percent"?$input['discount_amount']:null,
                        'discount_type' => $input['value'] ?? null
                    ]);
                }
                if ($input['field_name'] == 'discount') {
                    $discountAmount = 0;
                    if ($input['discount_type'] === "Flat") {
                        $discountAmount = $input['value'];
                    } else if ($input['discount_type'] === "Percent") {
                        $discountAmount = ($findInvoice->sub_total * $input['value']) / 100;
                    }

                    $findInvoice->update([
                        'discount' => $discountAmount,
                        'percentage' => $input['discount_type'] === "Percent"?$input['value']:null,
                        'discount_type' => $input['discount_type'] ?? null
                    ]);
                }
                if ($input['field_name'] == 'customer_id') {
                    $findInvoice->update([
                        'customer_id' => $input['value']
                    ]);
                }
                if ($input['field_name'] == 'transaction_mode_id') {
                    $findInvoice->update([
                        'transaction_mode_id' => $input['value']
                    ]);
                }
                return response()->json([
                    'status' => ResponseAlias::HTTP_OK,
                    'message' => 'success',
                ], ResponseAlias::HTTP_OK);

            } catch (\Exception $e) {
                Log::error('Error updating invoices: ' . $e->getMessage());
                return response()->json(['error' => 'An error occurred'], 500);
            }
        }

        $allowedParticularFieldNames = ['items'];
        if (in_array($input['field_name'], $allowedParticularFieldNames)) {
            DB::beginTransaction();
            try {
                $findStock = StockItemModel::find($input['value']['id']);
                if (!$findStock) {
                    return response()->json(['status' => ResponseAlias::HTTP_NOT_FOUND, 'message' => 'Stock item not found'], ResponseAlias::HTTP_NOT_FOUND);
                }

                $findInvoice = InvoiceTempModel::find($input['invoice_id'])
                    ?? InvoiceTempModel::where('config_id', $this->domain['config_id'])->where('is_active', 1)->first();

                if (!$findInvoice) {
                    $findInvoice = $this->createCustomerForSales($this->domain['global_id']);
                    $findInvoice->update(['is_active' => 1])->refresh();
                }

                // Check for existing item
                $findStockInTempItem = InvoiceItemTempModel::where('invoice_id', $findInvoice->id)
                    ->where('stock_item_id', $input['value']['id'])
                    ->first();

                if (!$findStockInTempItem) {
                    InvoiceItemTempModel::create([
                        'stock_item_id' => $input['value']['id'],
                        'invoice_id' => $findInvoice->id,
                        'quantity' => $input['value']['quantity'],
                        'purchase_price' => $findStock->purchase_price,
                        'sales_price' => $findStock->sales_price,
                        'custom_price' => false,
                        'sub_total' => $findStock->sales_price,
                        'is_print' => true,
                    ]);
                } else {
                    $quantityToAdd = $input['value']['quantity'] ?? 1;
                    $findStockInTempItem->increment('quantity', $quantityToAdd);
                    $findStockInTempItem->update([
                        'sub_total' => $findStockInTempItem->quantity * $findStock->sales_price,
                    ]);
                }

                // Update invoice total
                $findInvoice->update(['sub_total' => InvoiceItemTempModel::where('invoice_id', $findInvoice->id)->sum('sub_total')]);

                DB::commit();
                return response()->json(['status' => ResponseAlias::HTTP_OK, 'message' => 'success'], ResponseAlias::HTTP_OK);
            } catch (\Exception $e) {
                DB::rollBack();
                Log::error("Error updating invoices: {$e->getMessage()} on line {$e->getLine()} in file {$e->getFile()}");
                return response()->json(['error' => 'An error occurred'], ResponseAlias::HTTP_INTERNAL_SERVER_ERROR);
            }
        }

        return response()->json([
            'status' => ResponseAlias::HTTP_NOT_FOUND,
            'message' => 'An error occurred',
        ], ResponseAlias::HTTP_NOT_FOUND);
    }

    public function invoiceDetails(Request $request)
    {
        $invoiceId = $request->query('invoice_id');
        $findInvoice = InvoiceTempModel::find($invoiceId);
        if (!$invoiceId) {
            $findInvoice = InvoiceTempModel::where('config_id', $this->domain['config_id'])->where('is_active',1)->first();
        }

        $invoiceDetails = InvoiceTempModel::getInvoiceDetails($findInvoice);

        // Return JSON Response
        return response()->json([
            'status' => ResponseAlias::HTTP_OK,
            'message' => 'success',
            'data' => $invoiceDetails
        ], ResponseAlias::HTTP_OK);
    }

    public function posSalesComplete(int $id)
    {
        $domain = $this->domain;
        $findInvoice = InvoiceTempModel::find($id);
        if (!$findInvoice || $findInvoice->is_active==0) {
            return response()->json([
                'status' => ResponseAlias::HTTP_NOT_FOUND,
                'message' => 'Invoice not found',
            ], ResponseAlias::HTTP_NOT_FOUND);
        }
        $customer = CustomerModel::where([
            'domain_id' => $this->domain['id'],
            'Name' => 'Default',
            'mobile' => $domain['license_no'],
            'is_default_customer' => 1
        ])->first();

        $input['config_id'] = $this->domain['config_id'];
        $input['customer_id'] = $customer->id;
        $input['created_by_id'] = $this->domain['user_id'];
        $input['sales_by_id'] = $findInvoice->sales_by_id;
        $input['sub_total'] = $findInvoice->sub_total;
        $input['total'] = $findInvoice->sub_total-$findInvoice->discount;
        $input['discount_type'] = $findInvoice->discount_type;
        $input['discount_calculation'] = $findInvoice->percentage;
        $input['discount'] = $findInvoice->discount;
        $input['payment'] = $findInvoice->payment;
        $input['transaction_mode_id'] = $findInvoice->transaction_mode_id;
        $input['sales_form'] = 'pos';

        // Start Database Transaction to Ensure Data Consistency
        DB::beginTransaction();

        try {
            // Create Sales Record
            $sales = SalesModel::create($input);
            $sales->refresh();

            // Insert Sales Items
            SalesItemModel::insertSalesItemsForPos($sales, $findInvoice->invoiceItems->toArray());

            // Fetch Sales Data for Response
            $salesData = SalesModel::getShow($sales->id, $this->domain);

            // Stock Maintenance Logic (Auto Approval)
            $findConfig = ConfigModel::find($this->domain['config_id']);

            if (
                $findConfig->is_sales_auto_approved
            ) {
                $sales->update(['process' => 'approved']);
                $sales->update(['approved_by_id' => $this->domain['user_id']]);

                if ($sales->salesItems->count() > 0) {
                    foreach ($sales->salesItems as $item) {
                        StockItemHistoryModel::openingStockQuantity($item, 'sales', $this->domain);
                    }
                }
            }

            // delete invoice items & clear invoice activation
            foreach ($findInvoice->invoiceItems as $item) {
                $item->delete();
            }

            $findInvoice->update([
                'discount_type' => 'Percent',
                'payment' => null,
                'customer_id' => null,
                'discount' => null,
                'percentage' => null,
                'transaction_mode_id' => null,
                'sales_by_id' => null,
                'sub_total' => null,
                'sales_by_name' => null,
                'is_active' => 0
            ]);
            // Commit Transaction
            DB::commit();

            // Send Success Response
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Sales updated successfully.',
                'data' => $salesData,
            ]);
        } catch (Exception $e) {
            // Rollback Transaction on Failure
            DB::rollBack();

            // Log the Error (For Debugging Purposes)
            \Log::error('Sales transaction failed: ' . $e->getMessage());

            // Send Error Response
            return response()->json([
                'message' => 'An error occurred while processing the sale.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function posSalesProcess(PosSalesProcessRequest $request)
    {
        $validated = $request->validated();

        $domain = $this->domain;

        $alreadyExists = PosSaleModel::where('device_id', $validated['device_id'])
            ->where('sync_batch_id', $validated['sync_batch_id'])
            ->whereIn('process', [PosSaleProcess::PENDING, PosSaleProcess::PROCESSING])
            ->exists();

        if ($alreadyExists) {
            return response()->json([
                'status'  => 200,
                'success' => true,
                'message' => 'Batch already received'
            ]);
        }

        $sync = PosSaleModel::create([
            'device_id'      => $validated['device_id'],
            'sync_batch_id'  => $validated['sync_batch_id'],
            'config_id'      => $domain['config_id'],
            'created_by_id'  => $domain['user_id'],
            'content'        => $validated['content']
        ]);

        $domainArray = [
            'acc_config'        => $domain['acc_config'],
            'domain_id'         => $domain['domain_id'],
            'config_id'         => $domain['config_id'],
            'user_id'           => $domain['user_id'],
            'inv_config'        => $domain['inv_config'],
            'warehouse_id'      => $domain['warehouse_id'],
            'is_auto_approve'   => $domain['is_sales_auto_approved'] ?? false,
        ];

        ProcessPosSalesJob::dispatch($sync->id, $domainArray);

        return response()->json([
            'status'  => 201,
            'success' => true,
            'message' => 'Sales sync queued successfully.',
        ]);
    }




}
