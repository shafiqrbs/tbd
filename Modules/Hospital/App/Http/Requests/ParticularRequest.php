<?php

namespace Modules\Hospital\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ParticularRequest extends FormRequest
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
                    'name' => 'required|string|nullable',
                    'particular_type_master_id' => 'integer|nullable',
                    'particular_type_id' => 'integer|nullable',
                    'created_by_id' => 'integer|nullable',
                    'category_id' => 'integer|nullable',
                    'diagnostic_room_id' => 'integer|nullable',
                    'diagnostic_department_id' => 'integer|nullable',
                    'employee_id' => 'integer|nullable',
                    'identity_card_no' => 'integer|nullable',
                    'price' => 'numeric|nullable',
                    'over_head_cost' => 'numeric|nullable',
                    'minimum_price' => 'numeric|nullable',
                    'commission' => 'numeric|nullable',
                    'room_id' => 'integer|nullable',
                    'unit_id' => 'integer|nullable',
                    'store_id' => 'integer|nullable',
                    'patient_type_id' => 'integer|nullable',
                    'gender_mode_id' => 'integer|nullable',
                    'payment_mode_id' => 'integer|nullable',
                    'financial_service_id' => 'integer|nullable',
                    'patient_mode_id' => 'integer|nullable',
                    'treatment_mode_id' => 'integer|nullable',
                    'cabin_mode_id' => 'integer|nullable',
                    'report_format' => 'integer|nullable',
                    'specimen' => 'string|nullable',
                    'content' => 'string|nullable',
                    'report_machine_name' => 'string|nullable',
                    'instruction' => 'string|nullable',
                    'status' => 'boolean',
                    'is_report_format' => 'boolean',
                    'is_custom_report' => 'boolean',
                    'is_available' => 'boolean',
                    'report_unit_hide' => 'boolean',
                    'is_report_content' => 'boolean',
                    'is_machine_format' => 'boolean',
                    'additional_field' => 'boolean',
                    'discount_valid' => 'boolean',
                    'is_attachment' => 'boolean',
                    'phone_no' => 'string|nullable',
                    'start_hour' => 'string|nullable',
                    'end_hour' => 'string|nullable',
                    'weekly_off_day' => 'string|nullable',
                    'email' => 'string|nullable',
                    'specialist' => 'string|nullable',
                    'educational_degree' => 'string|nullable',
                    'doctor_signature' => 'string|nullable',
                    'signature' => 'string|nullable',
                    'pathologist_signature' => 'string|nullable',
                    'current_job' => 'string|nullable',
                    'remark' => 'string|nullable',
                    'visit_time' => 'string|nullable',
                    'designation' => 'string|nullable',
                    'address' => 'string|nullable',
                    'mobile' => 'string|nullable',
                ];
            }
            case 'PUT':
            case 'POST':
            {
                return [
                    'name' => 'required|string|nullable',
                    'particular_type_master_id' => 'integer|nullable',
                    'particular_type_id' => 'integer|nullable',
                    'created_by_id' => 'integer|nullable',
                    'category_id' => 'integer|nullable',
                    'diagnostic_room_id' => 'integer|nullable',
                    'diagnostic_department_id' => 'integer|nullable',
                    'employee_id' => 'integer|nullable',
                    'identity_card_no' => 'integer|nullable',
                    'price' => 'numeric|nullable',
                    'over_head_cost' => 'numeric|nullable',
                    'minimum_price' => 'numeric|nullable',
                    'commission' => 'numeric|nullable',
                    'room_id' => 'integer|nullable',
                    'unit_id' => 'integer|nullable',
                    'store_id' => 'integer|nullable',
                    'patient_type_id' => 'integer|nullable',
                    'financial_service_id' => 'integer|nullable',
                    'gender_mode_id' => 'integer|nullable',
                    'payment_mode_id' => 'integer|nullable',
                    'patient_mode_id' => 'integer|nullable',
                    'treatment_mode_id' => 'integer|nullable',
                    'cabin_mode_id' => 'integer|nullable',
                    'report_format' => 'integer|nullable',
                    'specimen' => 'string|nullable',
                    'content' => 'string|nullable',
                    'report_machine_name' => 'string|nullable',
                    'instruction' => 'string|nullable',
                    'status' => 'boolean',
                    'is_report_format' => 'boolean|nullable',
                    'is_custom_report' => 'boolean|nullable',
                    'is_available' => 'boolean|nullable',
                    'report_unit_hide' => 'boolean',
                    'is_report_content' => 'boolean',
                    'is_machine_format' => 'boolean',
                    'additional_field' => 'boolean',
                    'discount_valid' => 'boolean',
                    'is_attachment' => 'boolean',
                    'phone_no' => 'string|nullable',
                    'start_hour' => 'string|nullable',
                    'end_hour' => 'string|nullable',
                    'weekly_off_day' => 'string|nullable',
                    'email' => 'string|nullable',
                    'specialist' => 'string|nullable',
                    'educational_degree' => 'string|nullable',
                    'doctor_signature' => 'string|nullable',
                    'signature' => 'string|nullable',
                    'pathologist_signature' => 'string|nullable',
                    'current_job' => 'string|nullable',
                    'remark' => 'string|nullable',
                    'visit_time' => 'string|nullable',
                    'designation' => 'string|nullable',
                    'address' => 'string|nullable',
                    'mobile' => 'string|nullable',
                ];
            }

            default:break;
        }
    }

}
