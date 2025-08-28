<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;

class RequisitionBoardModel extends Model
{
    protected $table = 'inv_requisition_board';
    public $timestamps = true;
    protected $guarded = ['id'];

    protected $fillable = [
    ];

    public function requisition_matrix(){
        return $this->hasMany(RequisitionMatrixBoardModel::class,'requisition_board_id');
    }

    public static function boot() {

        parent::boot();
        self::creating(function ($model) {
            $date =  new \DateTime("now");
            $model->created_at = $date;
        });

        self::updating(function ($model) {
            $date =  new \DateTime("now");
            $model->updated_at = $date;
        });
    }
}
