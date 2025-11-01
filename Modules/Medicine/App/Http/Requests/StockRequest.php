<?php

namespace Modules\Medicine\App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\App\Entities\Product;

class StockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        switch ($this->method()) {
            case 'PUT':
            case 'POST':
                return [
                    'category_id' => 'nullable',
                    'name' => 'required|string|max:255', // ✅ removed |unique:name
                    'opd_quantity' => 'nullable',
                    'medicine_dosage_id' => 'nullable',
                    'medicine_bymeal_id' => 'nullable',
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
            $exists = DB::table('inv_product')
                ->where('name', $this->name)
                ->when($this->isMethod('PUT'), function ($query) {
                    $query->where('id', '!=', $this->route('id'));
                })
                ->exists();
            if ($exists) {
                $validator->errors()->add('name', 'The name has already been taken.');
            }
        });
    }
}
