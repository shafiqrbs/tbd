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
        // Check if we are updating (when an 'id' is present) or creating (when 'id' is not present)
        $user = $this->route('user'); // Make sure to match the route parameter name.

        switch ($this->method()) {
            case 'GET':
            case 'DELETE':
            {
                return [];
            }
            case 'POST':
            {
                return [
                    'name' => 'required|string',
                    'username' => [
                        'required',
                        Rule::unique('Modules\Core\App\Entities\User', 'username')  // Ensure using `users` table - check your actual table
                    ],
                    'mobile' => [
                        'required',
                        Rule::unique('Modules\Core\App\Entities\User', 'mobile')  // Ensure using `users` table
                    ],

                    'email' => [
                        'required',
                        'email',
                        Rule::unique('Modules\Core\App\Entities\User', 'email')  // Ensure using `users` table
                    ],
                    'password' => 'required|min:6',
                    'employee_group_id' => 'required',
                    'employee_id' => 'nullable',
                    'designation_id' => 'integer|nullable',
                    'department_id' => 'integer|nullable',
                    'dob' => 'string|nullable',
                    'gender' => 'string|nullable',
                    'address' => 'string|nullable',
                    'confirm_password' => 'required|same:password',
                ];
            }

            case 'PUT':
            case 'PATCH':
            {
                return [
                    'name' => 'required|string',
                    'username' => [
                        'required',
                        Rule::unique('Modules\Core\App\Entities\User', 'username')->ignore($user) // Ignore the current user's username on update
                    ],
                    'mobile' => [
                        'required',
                        Rule::unique('Modules\Core\App\Entities\User', 'mobile')->ignore($user) // Ignore on update
                    ],
                    'email' => [
                        'required',
                        'email',
                        Rule::unique('Modules\Core\App\Entities\User', 'email')->ignore($user) // Ignore on update
                    ],
                    'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
                    'digital_signature' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
                    'alternative_email' => 'nullable|email',
                    'designation_id' => 'nullable',
                    'department_id' => 'nullable',
                    'location_id' => 'nullable',
                    'address' => 'nullable|string',
                    'about_me' => 'nullable|string',
                    'enabled' => 'required',
                    'dob' => 'string|nullable',
                    'gender' => 'string|nullable',
                    'employee_id' => 'nullable',
                    'employee_group_id' => 'required',
                    'access_control_role' => 'nullable|array',
                    'android_control_role' => 'nullable|array',
                ];
            }
            default:
                break;
        }
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array
     */
    public function messages()
    {
        return [
            'name.required' => 'Name field must be required',
            'name.string' => 'Name field must be a string',
            'username.required' => 'Username field must be required',
            'mobile.required' => 'Mobile field must be required',
            'mobile.numeric' => 'Mobile field must contain only numbers',
            'email.required' => 'Email field must be required',
            'email.email' => 'Email field must be valid',
            'password.required' => 'Password field is required for new users',
            'password.min' => 'Password must be at least 6 characters long',
            'confirm_password.required' => 'Confirm password field must be required',
            'confirm_password.same' => 'Confirm password must match the provided password',
        ];
    }
}
