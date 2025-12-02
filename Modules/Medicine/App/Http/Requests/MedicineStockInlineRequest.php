<?php

namespace Modules\Medicine\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MedicineStockInlineRequest extends FormRequest
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
    /*public function rules(): array
    {

        switch($this->method())
        {

            case 'PUT':
            case 'POST':
            {
                return [
                    'category_id' => 'nullable',
                    'opd_quantity' => 'nullable',
                    'medicine_dosage_id' => 'nullable',
                    'medicine_bymeal_id' => 'nullable',
                    'admin_status' => 'nullable',
                    'ipd_status' => 'nullable',
                    'opd_status' => 'nullable',
                ];
            }

            default:break;
        }
    }*/

    public function rules(): array
    {
        switch ($this->method()) {
            case 'PUT':
            case 'POST':
                return [
                    'category_id' => 'nullable',
                    'product_name' => 'nullable|string',
                    'opd_quantity' => 'nullable',
                    'medicine_dosage_id' => 'nullable',
                    'medicine_bymeal_id' => 'nullable',
                    'admin_status' => 'nullable|boolean',
                    'ipd_status' => 'nullable|boolean',
                    'opd_status' => 'nullable|boolean',
                    'duration' => 'nullable',
                    'duration_mode' => 'nullable',
                ];

            default:
                return [];
        }
    }

}
