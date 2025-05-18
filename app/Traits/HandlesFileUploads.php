<?php

namespace App\Traits;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

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
     * @param array $validationRules
     * @param bool $preserveOriginalName
     * @return string|null
     * @throws \Exception
     */
    public function handleFileUpload(
        Request $request,
        mixed $model = null,
        string $attribute = 'image',
        string $directory = 'uploads',
        ?string $disk = 'public',
        array $validationRules = [],
        bool $preserveOriginalName = false
    ): ?string {
        $file = $request->file($attribute);

        if (!$file) {
            // Return existing file path if available
            return $model?->{$attribute} ?? null;
        }

        // Validate file if validation rules are provided
        if (!empty($validationRules)) {
            $validator = Validator::make(
                [$attribute => $file],
                [$attribute => $validationRules]
            );

            if ($validator->fails()) {
                throw new \Exception("File validation failed: " . $validator->errors()->first($attribute));
            }
        }

        try {
            // Delete old file if it exists and model is provided
            if ($model && !empty($model->{$attribute})) {
                $this->deleteFile($model->{$attribute}, $disk);
            }

            // Generate filename
            $filename = $preserveOriginalName
                ? $this->generateUniqueFilename($file, $directory, $disk)
                : null;

            // Store the new file
            $path = $filename
                ? $file->storeAs($directory, $filename, $disk)
                : $file->store($directory, $disk);

            return $path;

        } catch (\Exception $e) {
            throw new \Exception("File upload failed: " . $e->getMessage());
        }
    }

    /**
     * Handle multiple file uploads.
     *
     * @param Request $request
     * @param string $attribute
     * @param string $directory
     * @param string|null $disk
     * @param array $validationRules
     * @return array
     * @throws \Exception
     */
    public function handleMultipleFileUploads(
        Request $request,
        string $attribute = 'images',
        string $directory = 'uploads',
        ?string $disk = 'public',
        array $validationRules = []
    ): array {
        $files = $request->file($attribute);

        if (!$files || !is_array($files)) {
            return [];
        }

        $uploadedPaths = [];

        foreach ($files as $file) {
            if ($file instanceof UploadedFile) {
                // Create a temporary request with single file for validation
                $tempRequest = new Request();
                $tempRequest->files->set('temp_file', $file);

                try {
                    $path = $this->handleFileUpload(
                        $tempRequest,
                        null,
                        'temp_file',
                        $directory,
                        $disk,
                        $validationRules
                    );

                    if ($path) {
                        $uploadedPaths[] = $path;
                    }
                } catch (\Exception $e) {
                    // Cleanup any successfully uploaded files if one fails
                    foreach ($uploadedPaths as $uploadedPath) {
                        $this->deleteFile($uploadedPath, $disk);
                    }
                    throw $e;
                }
            }
        }

        return $uploadedPaths;
    }

    /**
     * Delete a file from storage.
     *
     * @param string $path
     * @param string|null $disk
     * @return bool
     */
    public function deleteFile(string $path, ?string $disk = 'public'): bool
    {
        try {
            if (Storage::disk($disk)->exists($path)) {
                return Storage::disk($disk)->delete($path);
            }
            return true; // File doesn't exist, consider it "deleted"
        } catch (\Exception $e) {
            // Log the error but don't throw exception
            \Log::warning("Failed to delete file: {$path}", ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Generate a unique filename while preserving the original extension.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string|null $disk
     * @return string
     */
    protected function generateUniqueFilename(UploadedFile $file, string $directory, ?string $disk = 'public'): string
    {
        $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $extension = $file->getClientOriginalExtension();
        $cleanName = Str::slug($originalName);

        $filename = $cleanName . '.' . $extension;
        $counter = 1;

        // Ensure filename is unique
        while (Storage::disk($disk)->exists($directory . '/' . $filename)) {
            $filename = $cleanName . '-' . $counter . '.' . $extension;
            $counter++;
        }

        return $filename;
    }

    /**
     * Get file URL from storage path.
     *
     * @param string|null $path
     * @param string|null $disk
     * @return string|null
     */
    public function getFileUrl(?string $path, ?string $disk = 'public'): ?string
    {
        if (!$path) {
            return null;
        }

        try {
            return Storage::disk($disk)->url($path);
        } catch (\Exception $e) {
            \Log::warning("Failed to generate URL for file: {$path}", ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Check if a file exists in storage.
     *
     * @param string $path
     * @param string|null $disk
     * @return bool
     */
    public function fileExists(string $path, ?string $disk = 'public'): bool
    {
        try {
            return Storage::disk($disk)->exists($path);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get file size in bytes.
     *
     * @param string $path
     * @param string|null $disk
     * @return int|null
     */
    public function getFileSize(string $path, ?string $disk = 'public'): ?int
    {
        try {
            return Storage::disk($disk)->size($path);
        } catch (\Exception $e) {
            return null;
        }
    }
}
