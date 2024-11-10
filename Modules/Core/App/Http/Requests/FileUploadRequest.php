<?php

namespace Modules\Core\App\Http\Requests;
use Illuminate\Foundation\Http\FormRequest;

class FileUploadRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\Rule|array|string>
     */
    public function rules(): array
    {

        switch($this->method())
        {
            case 'GET':
            case 'DELETE':
            {
                return [];
            }
            case 'POST':
            {
                return [
                    'file_type' => 'required|string',
                    'file' => 'required|file'
                ];
            }

            case 'PUT':
            default:break;
        }
    }

    public function messages()
    {
        return [
            'file_type.required' => 'File type field must be required',
            'file_type.string' => 'File type field must be string',
            'file.required' => 'File field must be required',
            'file.file' => 'File field must be file',
        ];
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
}
