<?php

namespace Modules\Accounting\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountJournalRequest extends FormRequest
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

            case 'POST':
            {
                return [
                    'voucher_type_id' => 'required|string|nullable',
                    'account_refno' => 'string|nullable',
                    'description' => 'string|nullable'
                ];
            }

            case 'PUT':
            case 'PATCH':
            {
                return [
                    'voucher_type_id' => 'required|string|nullable',
                    'account_refno' => 'string|nullable',
                    'description' => 'string|nullable'
                ];
            }
            default:break;
        }
    }

}
