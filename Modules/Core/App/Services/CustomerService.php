<?php

namespace Modules\Core\App\Services;

use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Filters\CustomerFilter;
use Modules\Core\App\Models\CustomerModel;

class CustomerService
{

    public function get(array $queryParams = [])
    {
        $queryBuilder = CustomerModel::with(['location'])->select('id','name','mobile','created_at')->latest();
        $query = resolve(CustomerFilter::class)->getResults([
            'builder' => $queryBuilder,
            'params' => $queryParams
        ]);
        return $query;
    }

}
