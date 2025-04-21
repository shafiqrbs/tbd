<?php

namespace Modules\Inventory\App\Models;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Domain\App\Models\DomainModel;
use Modules\Utility\App\Entities\Setting;
use Modules\Utility\App\Models\CurrencyModel;
use Modules\Utility\App\Models\SettingModel;


class ConfigProductModel extends Model
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



}
