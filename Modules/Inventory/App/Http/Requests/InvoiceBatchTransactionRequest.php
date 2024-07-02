<?php

namespace Modules\Inventory\App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class InvoiceBatchTransactionRequest extends FormRequest
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
            case 'GET':
            case 'DELETE':
            {
                return [];
            }
            case 'POST':
            {
                return [
                    'invoice_batch_id' => 'integer|required',
                    'created_by_id' => 'integer|nullable',
                    'sales_by_id' => 'integer|nullable',
                    'provision_discount' => 'integer|nullable',
                    'provision_mode' => 'string|nullable',
                    'discount_calculation' => 'integer|nullable',
                    'discount_type' => 'string|nullable',
                    'comment' => 'string|nullable',
                ];
            }

            case 'PUT':
            case 'PATCH':
            {
                return [
                    'name' => 'required|string',
                    'status' => 'required|boolean',
                    'parent' => 'integer|nullable',
                ];
            }
            default:break;
        }
    }

    public function messages()
    {
        return [
            'name.required' => 'Category group field must be required',
            'name.string' => 'Category group  field must be string',
            'status.required' => 'Category group status field must be required',
            'status.boolean' => 'Category group status field must be boolean',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
