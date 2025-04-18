<?php

namespace Modules\Inventory\App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Modules\Core\App\Models\CustomerModel;

class InvoiceTempModel extends Model
{
    const TABLE = 'table';
    const CUSTOMER = 'customer';
    const USER = 'user';

    protected $table = 'inv_invoice_table';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'config_id',
        'created_by_id',
        'table_id',
        'sales_by_id',
        'transaction_mode_id',
        'process',
        'is_active',
        'order_date',
        'sub_total',
        'payment',
        'table_nos',
        'discount_type',
        'total',
        'vat',
        'sd',
        'discount',
        'percentage',
        'discount_calculation',
        'discount_coupon',
        'remark',
        'invoice_mode',
        'customer_id',
        'serve_by_id'
    ];

    public function invoiceItems()
    {
        return $this->hasMany(InvoiceItemTempModel::class, 'invoice_id');
    }

    public function customer()
    {
        return $this->belongsTo(CustomerModel::class, 'customer_id');
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

    public static function getInvoiceTables($config,$type){
        return self::where('inv_invoice_table.config_id',$config)
            ->where('inv_invoice_table.invoice_mode', $type)
            ->leftjoin('inv_particular','inv_particular.id','=','inv_invoice_table.table_id')
            ->leftjoin('cor_customers','cor_customers.id','=','inv_invoice_table.customer_id')
            ->select([
                'inv_invoice_table.id as id',
                'inv_invoice_table.config_id',
                'inv_invoice_table.created_by_id',
                'inv_invoice_table.table_id',
                'inv_invoice_table.sales_by_id',
                'inv_invoice_table.transaction_mode_id',
                'inv_invoice_table.process',
                'inv_invoice_table.is_active',
                'inv_invoice_table.order_date',
                'inv_invoice_table.sub_total',
                'inv_invoice_table.payment',
                'inv_invoice_table.table_nos',
                'inv_invoice_table.discount_type',
                'inv_invoice_table.total',
                'inv_invoice_table.vat',
                'inv_invoice_table.sd',
                'inv_invoice_table.discount',
                'inv_invoice_table.percentage',
                'inv_invoice_table.discount_calculation',
                'inv_invoice_table.discount_coupon',
                'inv_invoice_table.remark',
                'inv_invoice_table.invoice_mode',
                'inv_invoice_table.serve_by_id',
                'inv_particular.name as particular_name',
                'inv_particular.slug as particular_slug',
                'cor_customers.id as customer_id',
                'cor_customers.name as customer_name',
                'cor_customers.mobile as customer_mobile',
            ])
            ->get()
            ->toArray();
    }


    public static function getInvoiceDetails($findInvoice){
        $data =  self::where('inv_invoice_table.id',$findInvoice->id)
            ->leftjoin('users as created_by','created_by.id','=','inv_invoice_table.created_by_id')
            ->leftjoin('users as sales_by','sales_by.id','=','inv_invoice_table.sales_by_id')
            ->leftjoin('inv_particular','inv_particular.id','=','inv_invoice_table.table_id')
            ->leftjoin('cor_customers','cor_customers.id','=','inv_invoice_table.customer_id')
            ->select([
                'inv_invoice_table.id as id',
                'inv_invoice_table.config_id',
                'inv_invoice_table.created_by_id',
                'inv_invoice_table.table_id',
                'inv_invoice_table.sales_by_id',
                'inv_invoice_table.transaction_mode_id',
                'inv_invoice_table.process',
                'inv_invoice_table.is_active',
                'inv_invoice_table.order_date',
                'inv_invoice_table.sub_total',
                'inv_invoice_table.payment',
                'inv_invoice_table.table_nos',
                'inv_invoice_table.discount_type',
                'inv_invoice_table.total',
                'inv_invoice_table.vat',
                'inv_invoice_table.sd',
                'inv_invoice_table.discount',
                'inv_invoice_table.percentage',
                'inv_invoice_table.discount_calculation',
                'inv_invoice_table.discount_coupon',
                'inv_invoice_table.remark',
                'inv_invoice_table.invoice_mode',
                'inv_invoice_table.serve_by_id',
                'inv_particular.name as table_name',
                'inv_particular.slug as table_slug',
                'cor_customers.id as customer_id',
                'cor_customers.name as customer_name',
                'cor_customers.mobile as customer_mobile',
                'created_by.username as created_by_name',
                'sales_by.username as sales_by_name',
            ])->with(['invoiceItems' => function ($query) {
                $query->select([
                    'inv_invoice_table_item.id',
                    'inv_invoice_table_item.stock_item_id as product_id',
                    'inv_invoice_table_item.stock_item_id',
                    'inv_stock.display_name',
                    'inv_invoice_table_item.invoice_id',
                    'inv_invoice_table_item.quantity',
                    'inv_invoice_table_item.purchase_price',
                    'inv_invoice_table_item.sales_price',
                    'inv_invoice_table_item.custom_price',
                    'inv_invoice_table_item.is_print',
                    'inv_invoice_table_item.sub_total',
                ])->join('inv_stock','inv_stock.id','=','inv_invoice_table_item.stock_item_id');
            },'customer'])
            ->first();
        return $data;
    }
}
