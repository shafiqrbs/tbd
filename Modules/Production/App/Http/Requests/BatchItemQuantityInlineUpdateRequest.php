<?php

namespace Modules\Production\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BatchItemQuantityInlineUpdateRequest extends FormRequest
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
                    'quantity' => 'required',
                    'type' => 'required|string|in:issue_quantity,receive_quantity,damage_quantity',
                    'batch_id' => 'required|integer',
                    'batch_item_id' => 'integer|required',
                ];
            }

            default:break;
        }
    }

}
