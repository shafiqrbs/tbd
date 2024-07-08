<?php

namespace Modules\Core\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SettingRequest extends FormRequest
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
                    'name' => 'required|string|nullable',
                    'setting_type_id' => 'required|integer|nullable',
                    'status' => 'boolean|nullable',
                ];
            }

            case 'PUT':
            {
                return [
                    'name' => 'required|string|nullable',
                    'setting_type_id' => 'required|integer|nullable',
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
