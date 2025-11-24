<?php

namespace Modules\Hospital\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MedicineDosageRequest extends FormRequest
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
                    'mode' => 'string|nullable',
                    'continue_mode' => 'string|nullable',
                    'created_by_id' => 'integer|nullable',
                    'name' => 'string|nullable',
                    'name_bn' => 'string|nullable',
                    'dosage_form' => 'string|nullable',
                    'quantity' => 'integer|nullable',
                    'instruction' => 'string|nullable',
                    'is_private' => 'boolean|nullable',
                ];
            }
            case 'PUT':
            case 'POST':
            {
                return [
                    'mode' => 'string|nullable',
                    'continue_mode' => 'string|nullable',
                    'created_by_id' => 'integer|nullable',
                    'name' => 'string|nullable',
                    'name_bn' => 'string|nullable',
                    'dosage_form' => 'string|nullable',
                    'quantity' => 'integer|nullable',
                    'instruction' => 'string|nullable',
                    'is_private' => 'boolean|nullable',
                ];
            }

            default:break;
        }
    }

}
