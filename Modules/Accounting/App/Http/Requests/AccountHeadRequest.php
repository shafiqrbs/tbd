<?php

namespace Modules\Accounting\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AccountHeadRequest extends FormRequest
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
                    'mother_account_id' => 'integer|nullable',
                    'parent_id' => 'integer|nullable',
                    'slug' => 'string|nullable',
                    'code' => 'string|nullable',
                ];
            }

            case 'PUT':
            case 'PATCH':
            {
                return [
                    'name' => 'required|string',
                    'account_mother_id' => 'string|nullable',
                ];
            }
            default:break;
        }
    }

}
