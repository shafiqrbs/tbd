<?php

namespace Modules\Accounting\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountVoucherHeadRequest extends FormRequest
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
                    'account_voucher_id' => 'required|integer',
                    'primary_head_id' => 'nullable|array',
                    'primary_head_id.*' => 'integer',
                    'secondary_head_id' => 'nullable|array',
                    'secondary_head_id.*' => 'integer',
                ];
            }

            default:break;
        }
    }

}
