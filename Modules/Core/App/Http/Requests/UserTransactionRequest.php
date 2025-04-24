<?php

namespace Modules\Core\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserTransactionRequest extends FormRequest
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
                    'user_id' => 'required|integer|nullable',
                    'max_discount' => 'required|float|nullable',
                    'status' => 'boolean|nullable',
                ];
            }

            case 'PUT':
            {
                return [
                    'user_id' => 'required|integer|nullable',
                    'max_discount' => 'required|float|nullable',
                    'status' => 'boolean|nullable',
                ];
            }
            case 'PATCH':
            {
                return [
                    'name' => 'required|string',
                ];
            }
            default:break;
        }
    }

}
