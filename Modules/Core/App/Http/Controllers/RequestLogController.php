<?php

namespace Modules\Core\App\Http\Controllers;


use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Core\App\Models\RequestLogModel;


class RequestLogController extends Controller
{
    public function index()
    {
        $logs = RequestLogModel::latest()->paginate(20);
        return view('request_logs.index', compact('logs'));
    }
}



