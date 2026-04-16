<?php

namespace Modules\Accounting\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountPosVoucherRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {

        switch($this->method())
        {

            case 'PATCH':
            case 'PUT':
            case 'POST':
            {
                return [
                    'transaction_mode_id' => 'nullable|string',
                    'customer_id' => 'nullable|integer',
                    'vendor_id' => 'nullable|integer',
                    'amount' => 'required|integer',
                    'payment_mode' => 'required|string',
                    'narration' => 'nullable|string',
                    'is_approve' => 'integer|boolean',
                    'is_sms' => 'integer|boolean'
                ];
            }
            default:break;
        }
    }

}
