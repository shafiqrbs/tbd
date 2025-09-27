<?php

namespace Modules\Production\App\Models;


use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\App\Entities\Product;
use Modules\Inventory\App\Models\ProductModel;


class ProductionItemAmendmentModel extends Model
{
    use HasFactory;

    protected $table = 'pro_item_amendment';
    public $timestamps = false;
    protected $guarded = ['id'];
    protected $fillable = [];

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

    public static function generateAmendment($domain,$pro_item_id,$data)
    {
        $input = [];
        $input['production_item_id'] = $pro_item_id;
        $input['created_by_id'] = $domain['user_id'];
        $input['content'] = json_encode($data);
        self::create($input);
    }
}
