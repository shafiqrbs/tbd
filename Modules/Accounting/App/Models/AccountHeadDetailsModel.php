<?php

namespace Modules\Accounting\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;


class AccountHeadDetailsModel extends Model
{
    use HasFactory;

    protected $table = 'acc_head_details';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'config_id',
        'account_id',
        'ifc_code',
        'swift_code',
        'is_cheque_book',
        'is_cheque_print',
        'code',
        'name',
        'amount',
        'sorting',
        'bank_method',
        'pan_it_no'
    ];


}
