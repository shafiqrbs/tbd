<?php

namespace Modules\Hospital\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TreatmentMedicineRequest extends FormRequest
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
                    'medicine_id' => 'integer|nullable',
                    'treatment_template_id' => 'integer|nullable',
                    'medicine_name' => 'string|nullable',
                    'generic' => 'string|nullable',
                    'dosage' => 'string|nullable',
                    'by_meal' => 'string|nullable',
                    'duration' => 'string|nullable',
                    'quantity' => 'integer|nullable',
                    'medicine_dosage_id' => 'integer|nullable',
                    'medicine_bymeal_id' => 'integer|nullable',
                ];
            }
            case 'PUT':
            case 'POST':
            {
                return [
                    'medicine_id' => 'integer|nullable',
                    'treatment_template_id' => 'integer|nullable',
                    'medicine_name' => 'string|nullable',
                    'generic' => 'string|nullable',
                    'dosage' => 'string|nullable',
                    'by_meal' => 'string|nullable',
                    'duration' => 'string|nullable',
                    'quantity' => 'integer|nullable',
                    'medicine_dosage_id' => 'integer|nullable',
                    'medicine_bymeal_id' => 'integer|nullable',
                ];
            }

            default:break;
        }
    }

}
