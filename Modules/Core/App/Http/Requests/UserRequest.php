<?php

namespace Modules\Core\App\Http\Requests;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
                    'name' => 'required|string',
                    'username' => 'required',
                    'mobile' => 'required|numeric',
                    'email' => 'required|email',
                    'password' => 'required',
                    'confirm_password' => 'required|same:password',
                ];
            }

            case 'PUT':
            case 'PATCH':
            {
                return [
                    'name' => 'required|string',
                    'username' => 'required',
                    'mobile' => 'required|numeric',
                    'email' => 'required|email',
                ];
            }
            default:break;
        }
    }

    public function messages()
    {
        return [
            'name.required' => 'Name field must be required',
            'name.string' => 'Name field must be string',
            'username.required' => 'Username field must be required',
            'username.unique' => 'Username field must be unique',
            'mobile.required' => 'Mobile field must be required',
            'mobile.integer' => 'Mobile field must be number',
            'email.required' => 'Email field must be required',
            'email.email' => 'Email field must be valid',
            'email.unique' => 'Email field must be unique',
            'password.required' => 'Password field must be required',
            'confirm_password.required' => 'Confirm password field must be required',
        ];
    }
}
