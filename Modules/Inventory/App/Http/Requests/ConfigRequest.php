<?php

namespace Modules\Inventory\App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Validation\Rule;

class ConfigRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {

        switch($this->method())
        {
            case 'PUT':
            case 'POST':
            {
                return [
                    'business_model_id' => 'required|integer',
                    'currency_id' => 'required|integer',
                    'country_id' => 'required|integer',
                    'address' => 'nullable|string',
                    'sku_warehouse' => 'boolean',
                    'sku_category' => 'boolean',
                    'vat_enable' => 'boolean',
                    'vat_percent' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                    'ait_enable' => 'boolean',
                    'ait_percent' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                    'zakat_enable' => 'boolean',
                    'zakat_percent' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                    'invoice_comment' => 'string|nullable',
                    'logo' => 'nullable',
                    'remove_image' => 'boolean',
                    'invoice_print_logo' => 'boolean',
                    'print_outstanding' => 'boolean',
                    'pos_print' => 'boolean',
                    'is_print_header' => 'boolean',
                    'is_invoice_title' => 'boolean',
                    'is_print_footer' => 'boolean',
                    'is_powered' => 'boolean',
                    'print_footer_text' => 'nullable|string',
                    'body_font_size' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                    'invoice_height' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                    'invoice_width' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                    'border_color' => 'nullable|string',
                    'border_width' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                    'print_left_margin' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                    'print_top_margin' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                    'custom_invoice' => 'boolean',
                    'bonus_from_stock' => 'boolean',
                    'is_unit_price' => 'boolean',
                    'zero_stock' => 'boolean',
                    'stock_item' => 'boolean',
                    'custom_invoice_print' => 'boolean',
                    'is_stock_history' => 'boolean',
                    'condition_sales' => 'boolean',
                    'store_ledger' => 'boolean',
                    'is_marketing_executive' => 'boolean',
                    'tlo_commission' => 'boolean',
                    'sales_return' => 'boolean',
                    'sr_commission' => 'boolean',
                    'due_sales_without_customer' => 'boolean',
                    'is_description' => 'boolean',
                    'is_zero_receive_allow' => 'boolean',
                    'is_purchase_by_purchase_price' => 'boolean',
                    'is_active_sms' => 'boolean',
                ];
            }
            default:break;
        }
    }

    public function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'success'   => false,
            'message'   => 'Form Validation errors',
            'data'      => $validator->errors()
        ]));
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
