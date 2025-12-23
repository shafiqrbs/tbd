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
            case 'PUT':
            case 'PATCH':
        {
            return [
                'ref_no' => 'nullable|string',
                'issue_date' => 'required|date',
                'description' => 'string|nullable',
                'debit' => 'required|integer',
                'credit' => 'required|integer',
                'voucher_id' => 'required|integer',
                'branch_id' => 'nullable|integer',
                'items' => 'required|array|min:2',
                'items.*.id' => 'required|integer',
                'items.*.mode' => 'required|in:debit,credit',
                'items.*.debit' => 'nullable|numeric|min:0',
                'items.*.credit' => 'nullable|numeric|min:0',
                'items.*.bankInfo' => 'nullable',
                'items.*.type' => 'required',

            ];
        }
            default:break;
        }
    }

}
