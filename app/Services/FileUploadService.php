<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Validator;
use Exception;

class FileUploadService
{
    private $uploadBasePath;

    public function __construct($uploadBasePath = 'uploads')
    {
        $this->uploadBasePath = $uploadBasePath;
    }

    /**
     * Handle the file uploads.
     *
     * @param Request $request
     * @param array $files - Associative array of file field names and directories.
     * @param array $oldFiles - Old files to delete (optional).
     * @param array $validationRules - Validation rules for files (optional).
     * @return array - Paths of uploaded files.
     * @throws Exception
     */
    public function handleUploads(Request $request, array $files, array $oldFiles = [], array $validationRules = []): array
    {
        $uploadedPaths = [];

        foreach ($files as $field => $directory) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);

                // Validate file if rules are provided
                if (!empty($validationRules[$field])) {
                    $this->validateFile($file, $validationRules[$field]);
                }

                // Delete old files if provided
                if (!empty($oldFiles[$field])) {
                    $this->deleteOldFile($oldFiles[$field]);
                }

                // Generate a unique file name
                $filename = $this->generateFileName($field, $file);

                // Store the file in the specified directory
                $this->moveFile($file, $directory, $filename);

                // Store the file path
                $uploadedPaths[$field] = $directory . '/' . $filename;
            }
        }

        return $uploadedPaths;
    }

    /**
     * Validate a file based on rules.
     *
     * @param UploadedFile $file
     * @param array|string $rules - Accept both array and string format
     * @throws Exception
     */
    private function validateFile(UploadedFile $file, $rules)
    {
        // Convert string rules to array if needed
        if (is_string($rules)) {
            $rules = explode('|', $rules);
        }

        $validator = Validator::make(['file' => $file], ['file' => $rules]);

        if ($validator->fails()) {
            throw new Exception($validator->errors()->first());
        }
    }

    /**
     * Move the file to the specified directory.
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param string $filename
     * @throws Exception
     */
    private function moveFile(UploadedFile $file, string $directory, string $filename)
    {
        $uploadPath = public_path($this->uploadBasePath . '/' . $directory);

        // Create directory if it doesn't exist
        if (!file_exists($uploadPath) && !mkdir($uploadPath, 0755, true)) {
            throw new Exception('Failed to create directory: ' . $uploadPath);
        }

        try {
            $file->move($uploadPath, $filename);
        } catch (Exception $e) {
            throw new Exception('File upload failed: ' . $e->getMessage());
        }
    }

    /**
     * Generate a unique file name.
     *
     * @param string $field
     * @param UploadedFile $file
     * @return string
     */
    private function generateFileName(string $field, UploadedFile $file): string
    {
        return $field . '_' . time() . '-' . $file->getClientOriginalName();
    }

    /**
     * Delete the old file if it exists.
     *
     * @param string $filePath
     */
    public function deleteOldFile(string $filePath)
    {
        $oldFilePath = public_path($this->uploadBasePath . '/' . $filePath);
        if (file_exists($oldFilePath)) {
            unlink($oldFilePath);
        }
    }

    /**
     * Handle single file upload (convenience method)
     *
     * @param UploadedFile $file
     * @param string $directory
     * @param array|string $validationRules
     * @param string $customFilename
     * @return string
     * @throws Exception
     */
    public function uploadSingleFile(UploadedFile $file, string $directory, $validationRules = [], string $customFilename = null): string
    {
        // Validate file if rules are provided
        if (!empty($validationRules)) {
            $this->validateFile($file, $validationRules);
        }

        // Generate filename
        $filename = $customFilename ?: $this->generateFileName('file', $file);

        // Store the file
        $this->moveFile($file, $directory, $filename);

        return $directory . '/' . $filename;
    }
}
