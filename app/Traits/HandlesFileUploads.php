<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

trait HandlesFileUploads
{
    /**
     * Handle file upload and storage for a given model and attribute.
     *
     * @param Request $request
     * @param mixed|null $model
     * @param string $attribute
     * @param string $directory
     * @param string|null $disk
     * @return string|null
     */
    public function handleFileUpload(Request $request, mixed $model=null, string $attribute='image', string $directory='product', ?string $disk = 'r2'): ?string
    {
        if ($request->file($attribute)) {
            // Delete old file if it exists
            if (!empty($model->$attribute)) {
                Storage::disk($disk)->delete($model->$attribute);
            }

            // Store the new file
            return $request->file($attribute)->store($directory, $disk);
        }

        // If no upload, return the existing file path
        return $model->$attribute;
    }
}
