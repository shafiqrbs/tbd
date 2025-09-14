<?php

namespace Modules\Hospital\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReferredRequest extends FormRequest
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

            case 'PUT':
            case 'POST':
            {
                return [
                    'comment'       => 'nullable|string',
                    'opd_room_id'   => 'nullable|integer',
                    'referred_id'   => 'nullable|integer',
                    'referred_mode' => 'nullable|string',
                    'hospital'      => 'nullable|string',
                    'referred_name' => 'nullable|string',
                    'json_content' => 'nullable|text',
                ];
            }

            default:break;
        }
    }

}
