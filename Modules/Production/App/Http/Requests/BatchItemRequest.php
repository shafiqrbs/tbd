<?php

namespace Modules\Production\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BatchItemRequest extends FormRequest
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
                    'batch_id' => 'integer|required',
                    'production_item_id' => 'required|integer|nullable',
                    'issue_quantity' => 'required|integer|nullable',
                    'receive_quantity' => 'integer|nullable',
                    'damage_quantity' => 'integer|nullable',
                ];
            }

            default:break;
        }
    }

}
