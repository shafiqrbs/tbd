<?php

namespace Modules\Accounting\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TransactionMethodModel extends Model
{
    use HasFactory;

    protected $table = 'uti_transaction_method';
    public $timestamps = true;
    protected $guarded = ['id'];


    public static function getTransactionMethodDropdown()
    {
        $query = self::select(['name', 'slug', 'id'])
            ->where([['status', 1]]);
        return $query->get();
    }

}
