<?php

namespace Modules\Inventory\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\CustomerModel;
use Modules\Core\App\Models\SettingTypeModel;
use Modules\Core\App\Models\UserModel;
use Modules\Core\App\Models\VendorModel;
use Modules\Domain\App\Models\DomainModel;
use Modules\Inventory\App\Http\Requests\RequisitionRequest;
use Modules\Inventory\App\Models\ConfigModel;
use Modules\Inventory\App\Models\InvoiceBatchItemModel;
use Modules\Inventory\App\Models\InvoiceBatchModel;
use Modules\Inventory\App\Models\InvoiceTempModel;
use Modules\Inventory\App\Models\ParticularModel;
use Modules\Inventory\App\Models\ProductModel;
use Modules\Inventory\App\Models\PurchaseModel;
use Modules\Inventory\App\Models\RequisitionItemModel;
use Modules\Inventory\App\Models\RequisitionMatrixBoardModel;
use Modules\Inventory\App\Models\RequisitionModel;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\SalesModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;
use Modules\Utility\App\Models\SettingModel;
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
            InvoiceTempModel::create([
                'config_id' => $this->domain['config_id'],
                'created_by_id' => $this->domain['user_id'],
                'customer_id' => $findDefaultCustomer->id,
                'is_active' => false,
                'invoice_mode' => 'customer',
                'created_at' => now(),
            ]);
        }

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
                InvoiceTempModel::where('id', $input['invoice_id'])
                    ->update(['is_active' => 1]);

                return response()->json([
                    'status' => ResponseAlias::HTTP_OK,
                    'message' => 'success',
                ], ResponseAlias::HTTP_OK);

            } catch (\Exception $e) {
                Log::error('Error updating invoices: ' . $e->getMessage());
                return response()->json(['error' => 'An error occurred'], 500);
            }
        }

        $allowedParticularFieldNames = ['sales_by_id','amount','discount','customer_id'];
        if (in_array($input['field_name'], $allowedParticularFieldNames)) {
            try {
                $findInvoice = InvoiceTempModel::find($input['invoice_id']);
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
                if ($input['field_name'] == 'discount') {
                    $findInvoice->update([
                        'discount' => $input['value']
                    ]);
                }
                if ($input['field_name'] == 'customer_id') {
                    $findInvoice->update([
                        'customer_id' => $input['value']
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

        return response()->json([
            'status' => ResponseAlias::HTTP_NOT_FOUND,
            'message' => 'An error occurred',
        ], ResponseAlias::HTTP_NOT_FOUND);
    }
}
