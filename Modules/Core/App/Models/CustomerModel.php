<?php

namespace Modules\Core\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class CustomerModel extends Model
{
    use HasFactory;

    protected $table = 'cor_customers';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'name',
        'mobile'
    ];


    public static function getAllCustomers(){
        $data = self::where(['status'=>1])->whereNotNull('mobile')->orderBy('name','ASC')
            ->select([
                'customers.id as id',
                'customers.name as name',
                'customers.mobile as mobile'
            ])
            ->get()->toArray();
        return $data;
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(LocationModel::class);
    }
}
