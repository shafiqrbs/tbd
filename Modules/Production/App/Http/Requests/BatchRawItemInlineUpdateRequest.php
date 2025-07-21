<?php

namespace Modules\Production\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BatchRawItemInlineUpdateRequest extends FormRequest
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
                    'value' => 'required',
                    'type' => 'required|string|in:raw_issue_quantity',
                    'expense_id' => 'required',
                ];
            }

            default:break;
        }
    }

}
