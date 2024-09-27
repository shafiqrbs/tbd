<?php

namespace Modules\Core\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;


class RequestLogModel extends Model
{
    use HasFactory;

    protected $table = 'cor_request_logs';
    use HasFactory;

    protected $fillable = [
        'controller', 'action', 'method', 'url', 'ip_address', 'user_agent', 'requested_at',
    ];

}
