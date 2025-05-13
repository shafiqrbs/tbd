<?php

namespace Modules\Production\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Modules\Accounting\App\Entities\AccountJournal;
use Modules\AppsApi\App\Services\GeneratePatternCodeService;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Core\App\Models\CustomerModel;
use Modules\Core\App\Models\UserModel;
use Modules\Domain\App\Models\DomainModel;
use Modules\Inventory\App\Models\ConfigSalesModel;
use Modules\Inventory\App\Models\ProductModel;
use Modules\Inventory\App\Models\SalesItemModel;
use Modules\Inventory\App\Models\SalesModel;
use Modules\Inventory\App\Models\StockItemHistoryModel;
use Modules\Inventory\App\Models\StockItemModel;
use Modules\Production\App\Entities\ProductionIssue;
use Modules\Production\App\Http\Requests\BatchItemQuantityInlineUpdateRequest;
use Modules\Production\App\Http\Requests\BatchItemRequest;
use Modules\Production\App\Http\Requests\BatchRequest;
use Modules\Production\App\Http\Requests\IssueRequest;
use Modules\Production\App\Models\ProductionBatchItemModel;
use Modules\Production\App\Models\ProductionBatchModel;
use Modules\Production\App\Models\ProductionElements;
use Modules\Production\App\Models\ProductionExpense;
use Modules\Production\App\Models\ProductionIssueItemModel;
use Modules\Production\App\Models\ProductionIssueModel;
use Modules\Production\App\Models\ProductionItems;
use Modules\Production\App\Models\ProductionStockHistory;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class ProductionIssueController extends Controller
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

    public function index(Request $request)
    {
        $data = ProductionIssueModel::getRecords($request, $this->domain);
        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode([
            'message' => 'success',
            'status' => Response::HTTP_OK,
            'total' => $data['count'],
            'data' => $data['entities']
        ]));
        $response->setStatusCode(Response::HTTP_OK);
        return $response;
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(Request $request)
    {
        $input = $request->input();
        $input['config_id'] = $this->domain['pro_config'];
        $input['created_by_id'] = $this->domain['user_id'];

        // Start Database Transaction to Ensure Data Consistency
        DB::beginTransaction();

        try {
            $issue = ProductionIssueModel::create($input);
            $issue->refresh();

            // Commit Transaction
            DB::commit();

            // Send Success Response
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Issues updated successfully.',
                'data' => $issue,
            ]);
        } catch (Exception $e) {
            // Rollback Transaction on Failure
            DB::rollBack();

            // Log the Error (For Debugging Purposes)
            \Log::error('Issue transaction failed: ' . $e->getMessage());

            // Send Error Response
            return response()->json([
                'message' => 'An error occurred while processing the sale.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    public function show($id){
        try {
            $findIssue = ProductionIssueModel::with(['issueItems' => function ($query) {
                $query->select(
                    'pro_issue_item.id',
                    'pro_issue_item.config_id',
                    'pro_issue_item.stock_item_id',
                    'pro_issue_item.process',
                    'pro_issue_item.quantity',
                    'pro_issue_item.name as display_name',
                    'pro_issue_item.uom as unit_name',
                    'pro_issue_item.product_warehouse_id',
                    'pro_issue_item.purchase_price',
                    'pro_issue_item.sales_price',
                    'pro_issue_item.issue_date',
                    'pro_issue_item.batch_quantity',
                    'pro_issue_item.production_issue_id',
                    'inv_stock.quantity as stock_quantity'
                )->join('inv_stock', 'inv_stock.id', '=', 'pro_issue_item.stock_item_id');
            }])
                ->find($id);
            if (!$findIssue) {
                return response()->json([
                    'status' => 404,
                    'success' => false,
                    'message' => 'Data not found',
                ], 404);
            }

            // Return success response
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Production issue ',
                'data' => $findIssue,
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function update(IssueRequest $request, $id)
    {
        $input = $request->validated();
        $input['config_id'] = $this->domain['pro_config'];
        $input['created_by_id'] = $this->domain['user_id'];

        DB::beginTransaction();

        try {
            $findIssue = ProductionIssueModel::find($id);

            if (!$findIssue) {
                return response()->json([
                    'status' => 404,
                    'success' => false,
                    'message' => 'Data not found',
                ]);
            }

            $findIssue->update($input);
            $findIssue->refresh()->load('issueItems');

            // Delete and re-insert issue items
            $findIssue->issueItems()->delete();
            ProductionIssueItemModel::insertIssueItems($findIssue, $input['items']);

            // Approve the issue
            $findIssue->update([
                'approved_by_id' => $this->domain['user_id'],
                'process' => 'Approved'
            ]);

            // Reload issueItems after insert
            $findIssue->load('issueItems');

            foreach ($findIssue->issueItems as $item) {
                StockItemHistoryModel::openingStockQuantity($item, 'production-issue', $this->domain);
            }

            DB::commit();

            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Issues updated successfully.',
                'data' => $findIssue,
            ]);

        } catch (\Throwable $e) {
            DB::rollBack();

            \Log::error('Issue transaction failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'An error occurred while updating the issue.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }


    /*public function update(IssueRequest $request,$id)
    {
        $input = $request->validated();
        $input['config_id'] = $this->domain['pro_config'];
        $input['created_by_id'] = $this->domain['user_id'];
        // Start Database Transaction to Ensure Data Consistency
        DB::beginTransaction();

        try {
            $findIssue = ProductionIssueModel::find($id);
            if (!$findIssue) {
                return response()->json([
                    'status' => 404,
                    'success' => false,
                    'message' => 'Data not found',
                ]);
            }
            $findIssue->update($input);
            $findIssue->refresh();

            if ($findIssue->issueItems) {
                $findIssue->issueItems()->delete();
            }
            ProductionIssueItemModel::insertIssueItems($findIssue, $input['items']);

            // auto approve production issue
            $findIssue->update(['approved_by_id' => $this->domain['user_id'],'process' => 'Approved']);
            if ($findIssue->issueItems->count() > 0) {
                foreach ($findIssue->issueItems as $item) {
                    StockItemHistoryModel::openingStockQuantity($item, 'production-issue', $this->domain);
                }
            }

            // Commit Transaction
            DB::commit();

            // Send Success Response
            return response()->json([
                'status' => 200,
                'success' => true,
                'message' => 'Issues updated successfully.',
                'data' => $findIssue,
            ]);
        } catch (Exception $e) {
            // Rollback Transaction on Failure
            DB::rollBack();

            // Log the Error (For Debugging Purposes)
            \Log::error('Issue transaction failed: ' . $e->getMessage());

            // Send Error Response
            return response()->json([
                'message' => 'An error occurred while processing the sale.',
                'error' => $e->getMessage()
            ], 500);
        }
    }*/

}
