<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class TransactionService
{
    protected $responseService;
    protected $fileUploadService;

    public function __construct(ResponseService $responseService, FileUploadService $fileUploadService)
    {
        $this->responseService = $responseService;
        $this->fileUploadService = $fileUploadService;
    }

    /**
     * Store a new record within a transaction.
     *
     * @param Request $request
     * @param Model $model
     * @param array $validationRules
     * @param callable|null $customLogic
     * @param array $fileFields - Associative array of file field names and directories.
     * @param array $oldFiles - Old files to delete (optional).
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Model $model, array $validationRules, callable $customLogic = null, array $fileFields = [], array $oldFiles = [])
    {
        try {
            DB::beginTransaction();

            // Validasi request
            $validatedData = $request->validate($validationRules);

            // Handle file uploads jika ada
            $uploadedPaths = [];
            if (!empty($fileFields)) {
                $uploadedPaths = $this->fileUploadService->handleUploads($request, $fileFields, $oldFiles);
                logger('Uploaded Paths:', $uploadedPaths); // Log uploaded paths

                // Pastikan upload berhasil
                if (empty($uploadedPaths)) {
                    throw new Exception('File paths are missing after upload');
                }

                // Hapus input file dari request dan gabungkan dengan path hasil upload
                foreach ($fileFields as $field => $destinationPath) {
                    if ($request->hasFile($field)) {
                        $request->merge([$field => $uploadedPaths[$field] ?? null]); // Pastikan hanya path yang masuk
                    }
                }
            }

            // Debugging: Pastikan file fields sudah berubah menjadi string path
            logger('Request Data After Merge:', $request->all());

            // Isi model dengan data request yang sudah bersih dari UploadedFile
            $model->fill($request->except(array_keys($fileFields))); // Ambil data selain file
            foreach ($uploadedPaths as $field => $path) {
                $model->{$field} = $path; // Pastikan path file benar-benar masuk ke model
            }

            logger('Model Data Before Save:', $model->toArray());

            // Simpan model ke database
            $model->save();

            // Jalankan custom logic jika ada
            if ($customLogic) {
                $customLogic($request, $model);
            }

            DB::commit();
            return $this->responseService->success($model);
        } catch (Exception $e) {
            DB::rollBack();

            // Hapus file yang sudah di-upload jika terjadi error
            if (!empty($uploadedPaths)) {
                foreach ($uploadedPaths as $filePath) {
                    $this->fileUploadService->deleteOldFile($filePath);
                }
            }

            return $this->responseService->error($e->getMessage());
        }
    }

    /**
     * Update an existing record within a transaction.
     *
     * @param Request $request
     * @param Model $model
     * @param array $validationRules
     * @param callable|null $customLogic
     * @param array $fileFields - Associative array of file field names and directories.
     * @param array $oldFiles - Old files to delete (optional).
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Model $model, array $validationRules, callable $customLogic = null, array $fileFields = [], array $oldFiles = [])
    {
        try {
            DB::beginTransaction();

            // Validasi request
            $validatedData = $request->validate($validationRules);

            // Handle file uploads if any
            $uploadedPaths = [];
            if (!empty($fileFields)) {
                // Handle file uploads and return the new file paths
                $uploadedPaths = $this->fileUploadService->handleUploads($request, $fileFields, $oldFiles);
                logger('Uploaded Paths:', $uploadedPaths); // Log uploaded paths

                // Ensure upload was successful
                if (empty($uploadedPaths)) {
                    throw new Exception('File paths are missing after upload');
                }

                // Merge uploaded paths into request
                foreach ($fileFields as $field => $destinationPath) {
                    if ($request->hasFile($field)) {
                        $request->merge([$field => $uploadedPaths[$field] ?? null]); // Ensure only the path is included
                    }
                }

                // If old files are present, delete them (avoid keeping orphaned files)
                if (!empty($oldFiles)) {
                    foreach ($oldFiles as $oldFile) {
                        $this->fileUploadService->deleteOldFile($oldFile); // Ensure correct file deletion
                    }
                }
            }

            // Debugging: Ensure file fields have been correctly merged as paths
            logger('Request Data After Merge:', $request->all());

            // Fill the model with request data, excluding file fields
            $model->fill($request->except(array_keys($fileFields))); // Fill data excluding file fields

            // Ensure the uploaded paths (for file fields) are assigned to the model
            foreach ($uploadedPaths as $field => $path) {
                $model->{$field} = $path; // Assign file paths to the model (e.g., 'foto' field)
            }

            logger('Model Data Before Save:', $model->toArray());

            // Update the model
            $model->save();

            // Execute custom logic if provided
            if ($customLogic) {
                $customLogic($request, $model);
            }

            DB::commit();
            return $this->responseService->success($model);
        } catch (Exception $e) {
            DB::rollBack();

            // Delete uploaded files if an error occurs
            if (!empty($uploadedPaths)) {
                foreach ($uploadedPaths as $filePath) {
                    $this->fileUploadService->deleteOldFile($filePath); // Ensure this is correct
                }
            }

            return $this->responseService->error($e->getMessage());
        }
    }

    /**
     * Delete a record within a transaction.
     *
     * @param Model $model
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Model $model)
    {
        try {
            DB::beginTransaction();
            $model->delete();
            DB::commit();
            return $this->responseService->success(null, 'Record deleted successfully');
        } catch (Exception $e) {
            DB::rollBack();
            return $this->responseService->error($e->getMessage());
        }
    }

    /**
     * Get all records.
     *
     * @param Model $model
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAll(Model $model)
    {
        $records = $model::all();
        return $this->responseService->success($records);
    }

    /**
     * Get a record by ID.
     *
     * @param Model $model
     * @param int $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getById(Model $model, $id)
    {
        $record = $model::find($id);

        if (!$record) {
            return $this->responseService->error('Record not found', ResponseService::STATUS_NOT_FOUND);
        }

        return $this->responseService->success($record);
    }
}
