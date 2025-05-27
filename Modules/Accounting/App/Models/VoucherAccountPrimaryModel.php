<?php

namespace Modules\Accounting\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;


class VoucherAccountPrimaryModel extends Model
{
    use HasFactory;

    protected $table = 'acc_voucher_account_primary';
    public $timestamps = false;
    protected $guarded = ['id'];

}
