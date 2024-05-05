<?php

namespace Modules\Accounting\App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Modules\Accounting\App\Models\AccountingModel;
use Modules\Accounting\App\Models\AccountHeadModel;
use Modules\Accounting\App\Models\TransactionModeModel;
use Modules\AppsApi\App\Services\JsonRequestResponse;
use Modules\Domain\App\Http\Requests\DomainRequest;
use Modules\Core\App\Models\UserModel;
use Modules\Domain\App\Models\DomainModel;
use Modules\Inventory\App\Models\ConfigModel;
use Modules\Utility\App\Models\SettingModel;

class TransactionMethodController extends Controller
{
    protected $domain;

    public function __construct(Request $request)
    {
        $entityId = $request->header('X-Api-User');
        if ($entityId && !empty($entityId)){
            $entityData = UserModel::getUserData($entityId);
            $this->domain = $entityData;
        }
    }
    public function transactionMethodDropdown(Request $request)
    {
        $entity = AccountHeadModel::getTransactionMethodDropdown();
        $service = new JsonRequestResponse();
        return $service->returnJosnResponse($entity);
    }
}
