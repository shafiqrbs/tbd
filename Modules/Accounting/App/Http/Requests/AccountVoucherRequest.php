<?php

namespace Modules\Accounting\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountVoucherRequest extends FormRequest
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
                    'name' => 'required|string',
                    'voucher_type_id' => 'required|integer',
                    'short_name' => 'required|string',
                    'short_code' => 'required|string',
                    'mode' => 'required|string',
                ];
            }

            default:break;
        }
    }

}
