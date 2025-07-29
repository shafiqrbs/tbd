<?php

namespace Modules\Production\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BatchRequest extends FormRequest
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
            {
                return [
                    'remark' => 'nullable|string',
                    'issue_date' => 'nullable|date',
                    'receive_date' => 'nullable|date',
                    'process' => 'nullable|string',
                    'warehouse_id' => 'nullable',
                ];
            }
            case 'PUT':
            case 'POST':
            {
                return [
                    'config_id' => 'integer',
                    'mode' => 'required|string|nullable',
                    'warehouse_id' => 'nullable',
                ];
            }

            default:break;
        }
    }

}
