<?php

namespace Modules\Medicine\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\App\Entities\Product;

class MedicineRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        switch ($this->method()) {
            case 'PATCH':
            {
                return [
                    'category_id' => 'nullable',
                    'name' => 'required|string|max:255', // ✅ removed |unique:name
                    'opd_quantity' => 'nullable',
                    'medicine_dosage_id' => 'nullable',
                    'medicine_bymeal_id' => 'nullable',
                    'duration_mode_id' => 'nullable',
                    'admin_status' => 'nullable',
                    'ipd_status' => 'nullable',
                    'opd_status' => 'nullable',
                ];
            }
            case 'PUT':
            case 'POST':
                return [
                    'category_id' => 'nullable',
                    'name' => 'required|string|max:255', // ✅ removed |unique:name
                    'opd_quantity' => 'nullable',
                    'medicine_dosage_id' => 'nullable',
                    'medicine_bymeal_id' => 'nullable',
                    'duration_mode_id' => 'nullable',
                    'admin_status' => 'nullable',
                    'ipd_status' => 'nullable',
                    'opd_status' => 'nullable',
                ];
            default:
                return [];
        }
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {

            $stockId = $this->route('id'); // apiResource param
            $exists = DB::table('hms_medicine_details')
                ->where('hms_medicine_details.name', $this->name)
                ->where('hms_medicine_details.id', '!=', $stockId) // exclude self
                ->exists();

            if ($exists) {
                $validator->errors()->add('name', 'The name has already been taken.');
            }
        });
    }
}
