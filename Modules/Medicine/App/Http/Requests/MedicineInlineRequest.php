<?php

namespace Modules\Medicine\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MedicineInlineRequest extends FormRequest
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
                    'product_name' => 'nullable',
                    'company' => 'nullable',
                    'generic' => 'nullable',
                    'opd_quantity' => 'nullable',
                    'medicine_dosage_id' => 'nullable',
                    'medicine_bymeal_id' => 'nullable',
                ];
            }

            default:break;
        }
    }

}
