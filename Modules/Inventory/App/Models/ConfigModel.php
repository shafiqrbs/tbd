<?php

namespace Modules\Inventory\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\DB;
use Modules\Domain\App\Models\DomainModel;
use Modules\Utility\App\Entities\Setting;
use Modules\Utility\App\Models\CurrencyModel;
use Modules\Utility\App\Models\SettingModel;


class ConfigModel extends Model
{
    use HasFactory;

    protected $table = 'inv_config';
    public $timestamps = true;
    protected $guarded = ['id'];
    protected $fillable = [
        'business_model_id',
        'stock_format',
        'sku_warehouse',
        'sku_category',
        'ait_enable',
        'zakat_enable',
        'domain_id',
        'printer',
        'address',
        'path',
        'unit_commission',
        'border_color',
        'is_stock_history',
        'print_footer_text',
        'invoice_comment',
        'vat_percent',
        'ait_percent',
        'zakat_percent',
        'font_size_label',
        'font_size_value',
        'vat_reg_no',
        'multi_company',
        'vat_enable',
        'sd_percent',
        'sd_enable',
        'hs_code_enable',
        'vat_integration',
        'sd_enable',
        'bonus_from_stock',
        'condition_sales',
        'is_marketing_executive',
        'pos_print',
        'fuel_station',
        'zero_stock',
        'system_reset',
        'tlo_commission',
        'sr_commission',
        'sales_return',
        'store_ledger',
        'invoice_width',
        'print_top_margin',
        'print_margin_bottom',
        'header_left_width',
        'header_right_width',
        'print_margin_report_top',
        'is_print_header',
        'is_invoice_title',
        'print_outstanding',
        'is_print_footer',
        'invoice_prefix',
        'invoice_process',
        'customer_prefix',
        'production_type',
        'invoice_type',
        'border_width',
        'body_font_size',
        'sidebar_font_size',
        'invoice_font_size',
        'print_left_margin',
        'invoice_height',
        'left_top_margin',
        'is_unit_price',
        'body_top_margin',
        'sidebar_width',
        'body_width',
        'invoice_print_logo',
        'barcode_print',
        'custom_invoice',
        'custom_invoice_print',
        'show_stock',
        'is_powered',
        'remove_image',
        'stock_item',
        'due_sales_without_customer',
        'is_description',
        'is_zero_receive_allow',
        'is_purchase_by_purchase_price',
        'is_active_sms',
        'currency_id',
        'is_brand',
        'is_color',
        'is_size',
        'is_grade',
        'is_model',
        'is_multi_price',
        'is_measurement',
        'is_product_gallery',
        'is_batch_invoice',
        'is_provision',
        'country_id',
        'is_sales_auto_approved',
        'is_purchase_auto_approved',
        'is_pos',
        'pos_invoice_mode_id',
        'is_pay_first',
        'is_sku'

    ];


    public function configProduct(): HasOne
    {
        return $this->hasOne(ConfigProductModel::class, 'config_id', 'id');
    }

    public function configDiscount(): HasOne
    {
        return $this->hasOne(ConfigDiscountModel::class, 'config_id', 'id');
    }

    public function configPurchase(): HasOne
    {
        return $this->hasOne(ConfigPurchaseModel::class, 'config_id', 'id');
    }

    public function configSales(): HasOne
    {
        return $this->hasOne(ConfigSalesModel::class, 'config_id', 'id');
    }

    public function domain(): BelongsTo
    {
        return $this->belongsTo(DomainModel::class,'domain_id','id');
    }

     public function businessModel(): BelongsTo
    {
        return $this->belongsTo(SettingModel::class,'business_model_id','id');
    }

    public function currency(): BelongsTo
    {
        return $this->BelongsTo(CurrencyModel::class,'currency_id','id');
    }

    public function pos_invoice_mode(): BelongsTo
    {
        return $this->BelongsTo(SettingModel::class,'pos_invoice_mode_id','id');
    }

    public static function resetConfig($id){

        $table = 'inv_config';

        $columnsToExclude = [
            'id',
            'domain_id',
            'business_model_id',
            'stock_format',
            'vat_reg_no',
            'address',
            'path',
            'created_at',
            'updated_at'
        ];

        $booleanFields = [
            'is_stock_history' => true,
            'multi_company' => false,
            'bonus_from_stock' => false,
            'vat_enable' => false,
            'is_marketing_executive' => false,
            'pos_print' => false,
            'fuel_station' => false,
            'zero_stock' => false,
            'system_reset' => false,
            'tlo_commission' => false,
            'sr_commission' => false,
            'sales_return' => false,
            'store_ledger' => false,
            'is_print_header' => false,
            'is_invoice_title' => false,
            'print_outstanding' => false,
            'is_print_footer' => false,
            'invoice_print_logo' => false,
            'is_unit_price' => false,
            'barcode_print' => false,
            'custom_invoice' => false,
            'custom_invoice_print' => false,
            'show_stock' => false,
            'is_powered' => false,
            'remove_image' => false,
            'sku_category' => false,
            'sku_color' => false,
            'sku_size' => false,
            'sku_brand' => false,
            'sku_model' => false,
            'barcode_price_hide' => false,
            'barcode_color' => false,
            'barcode_size' => false,
            'barcode_brand' => false,
            'vat_mode' => false,
            'is_active_sms' => false,
            'is_zero_receive_allow' => false,
            'is_purchase_by_purchase_price' => false,
            'ait_enable' => false,
            'zakat_enable' => false,
            'stock_item' => false,
            'is_description' => false,
            'due_sales_without_customer' => false,
            'vat_integration' => false,
            'is_brand' => false,
            'is_color' => false,
            'is_size' => false,
            'is_model' => false,
            'is_multi_price' => false,
            'is_grade' => false,
            'is_measurement' => false,
            'is_product_gallery' => false,
            'is_batch_invoice' => false,
            'is_provision' => false,
            'is_sku' => false,
            'is_sales_auto_approved' => false,
            'is_purchase_auto_approved' => false,
            'is_pos' => false,
            'is_pay_first' => false,
            'sku_warehouse' => false,
            'pos_invoice_position' => false,
            'multi_kitchen' => false,
            'payment_split' => false,
            'item_addons' => false,
            'cash_on_delivery' => false,
            'is_online' => false,
            'hs_code_enable' => false,
            'sd_enable' => false,
            'is_category_item_quantity' => false,
        ];

        $columns = \Illuminate\Support\Facades\Schema::getColumnListing($table);
        $columnsToReset = array_diff($columns, $columnsToExclude);


        // Create SQL SET expressions
        $setStatements = [];
        foreach ($columnsToReset as $column) {
            if (array_key_exists($column, $booleanFields)) {
                // Handle boolean fields
                $value = $booleanFields[$column] ? 1 : 0;
                $setStatements[] = "`$column` = $value";
            } else {
                // Set other fields to NULL
                $setStatements[] = "`$column` = NULL";
            }
        }

        // Execute raw query with properly formatted SET statements
        DB::statement("UPDATE `$table` SET " . implode(', ', $setStatements) . " WHERE id = ?", [$id]);




    }


}
