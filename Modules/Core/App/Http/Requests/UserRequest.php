<?php

namespace Modules\Core\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
        $isUpdating = $this->route('id');

        return [
//            'name' => 'required|string',
//            'username' => 'required',
//            'mobile' => 'required|numeric',
//            'email' => 'required|email',
            'password' => !$isUpdating ? 'required|min:6' : 'nullable|min:6', // Required for creating, nullable for updating
            'confirm_password' => !$isUpdating ? 'required|same:password' : 'nullable|same:password',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg,gif',
            'digital_signature' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg',
            'alternative_email' => 'nullable|email',
            'designation_id' => 'nullable|string',
            'department_id' => 'nullable|string',
            'location_id' => 'nullable|string',
            'address' => 'nullable|string',
            'about_me' => 'nullable|string',
//            'enabled' => 'required|boolean',
//            'employee_group_id' => 'required|string',
            'access_control_role' => 'nullable|array',
            'android_control_role' => 'nullable|array',
        ];
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
