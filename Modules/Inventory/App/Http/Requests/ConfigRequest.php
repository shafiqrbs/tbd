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
            case 'PATCH':
            {
                return [
                    'business_model_id' => 'required|integer',
                    'vat_percent' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                    'ait_percent' => 'nullable|numeric|regex:/^\d+(\.\d{1,2})?$/',
                    'address' => 'string|nullable',
                    'invoice_comment' => 'string|nullable'

                    /*'business_model' => 'required|integer',
                    'vat_percent' => 'integer|nullable',
                    'ait_percent' => 'integer|nullable',
                    'address' => 'string|nullable',
                    'vat_reg_no' => 'string|nullable',
                    'invoice_comment' => 'string|nullable',
                    'multi_company' => 'boolean|nullable',
                    'vat_enable' => 'boolean|nullable',
                    'bonus_from_stock' => 'boolean|nullable',
                    'condition_sales' => 'boolean|nullable',
                    'is_marketing_executive' => 'boolean|nullable',
                    'pos_print' => 'boolean|nullable',
                    'fuel_station' => 'boolean|nullable',
                    'zero_stock' => 'boolean|nullable',
                    'tlo_commission' => 'boolean|nullable',
                    'sr_commission' => 'boolean|nullable',
                    'sales_return' => 'boolean|nullable',
                    'store_ledger' => 'boolean|nullable',
                    'is_print_header' => 'boolean|nullable',
                    'is_invoice_title' => 'boolean|nullable',
                    'print_outstanding' => 'boolean|nullable',
                    'show_stock' => 'boolean|nullable',
                    'is_powered' => 'boolean|nullable',
                    'remove_image' => 'boolean|nullable',
                    'is_unit_price' => 'boolean|nullable',
                    'barcode_print' => 'boolean|nullable',
                    'custom_invoice' => 'boolean|nullable',
                    'custom_invoice_print' => 'boolean|nullable',*/
                ];
            }
            default:break;
        }
    }

    /*public function messages()
    {
        return [
            'business_model_id.required' => 'Business model field is required.',
            'vat_percent.required' => 'Mobile Required .',
            'customer_unique_id.unique' => 'Customer already exists.',
        ];
    }*/

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
