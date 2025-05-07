<?php

namespace Modules\Production\App\Http\Controllers;

use App\Http\Controllers\Controller;
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

    /**
     * Show the form for creating a new resource.
     */
    public function store(IssueRequest $request)
    {
        $input = $request->validated();
        $input['config_id'] = $this->domain['pro_config'];
        $input['created_by_id'] = $this->domain['user_id'];

        // Start Database Transaction to Ensure Data Consistency
        DB::beginTransaction();

        try {
            $issue = ProductionIssueModel::create($input);
            $issue->refresh();

            ProductionIssueItemModel::insertIssueItems($issue, $input['items']);

            // auto approve production issue
            $issue->update(['approved_by_id' => $this->domain['user_id'],'process' => 'Approved']);
            if ($issue->issueItems->count() > 0) {
                foreach ($issue->issueItems as $item) {
                    $item['warehouse_id'] = $item['product_warehouse_id'];
                    unset($item['product_warehouse_id']);
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

}
