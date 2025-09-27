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
                    'name' => 'string|nullable',
                    'name_bn' => 'string|nullable',
                    'dosage_form' => 'string|nullable',
                    'quantity' => 'string|nullable',
                    'instruction' => 'string|nullable',
                ];
            }
            case 'PUT':
            case 'POST':
            {
                return [
                    'name' => 'string|nullable',
                    'name_bn' => 'string|nullable',
                    'dosage_form' => 'string|nullable',
                    'quantity' => 'integer|nullable',
                    'instruction' => 'string|nullable',
                ];
            }

            default:break;
        }
    }

}
