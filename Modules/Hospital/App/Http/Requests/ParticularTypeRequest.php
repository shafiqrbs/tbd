<?php

namespace Modules\Hospital\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ParticularTypeRequest extends FormRequest
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

                    'data_type'          => ['required','string'],
                    'operation_modes'          => ['array','nullable'],
                    'particular_type_id'          => ['required','integer','nullable'],
                    // allow either array (JSON) or a JSON-string that we normalize below
                ];
            }

            default:break;
        }
    }

}
