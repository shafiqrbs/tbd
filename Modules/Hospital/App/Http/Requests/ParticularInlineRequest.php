<?php

namespace Modules\Hospital\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ParticularInlineRequest extends FormRequest
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
                    'name' => 'nullable',
                    'unit_id' => 'nullable',
                    'opd_room_id' => 'nullable',
                    'opd_room_ids' => 'nullable',
                    'diagnostic_room_ids' => 'nullable',
                    'store_id' => 'nullable',
                    'status' => 'nullable',
                    'is_custom_report' => 'nullable',
                    'is_opd' => 'nullable',
                    'is_available' => 'nullable',
                    'report_format' => 'nullable',
                    'opd_referred' => 'nullable',
                    'investigation_group_id' => 'nullable',
                    'diagnostic_department_id' => 'nullable',
                    'financial_service_id' => 'nullable',
                    'diagnostic_room_id' => 'nullable',
                    'ordering' => 'nullable',
                    'price' => 'nullable',



                ];
            }

            default:break;
        }
    }

}
