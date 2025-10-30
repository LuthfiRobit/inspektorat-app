<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Exception;

class TransactionService
{
    protected $responseService;

    public function __construct(ResponseService $responseService)
    {
        $this->responseService = $responseService;
    }

    /**
     * Store a new record within a transaction.
     *
     * @param Request $request
     * @param Model $model
     * @param array $validationRules
     * @param callable|null $customLogic
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(Request $request, Model $model, array $validationRules, callable $customLogic = null)
    {
        try {
            DB::beginTransaction();

            // Fill and save the model
            $model->fill($request->all());
            $model->save();

            // Execute custom logic if provided
            if ($customLogic) {
                $customLogic($request, $model);
            }

            DB::commit();
            return $this->responseService->success($model);
        } catch (Exception $e) {
            DB::rollBack();
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, Model $model, array $validationRules, callable $customLogic = null)
    {
        try {
            DB::beginTransaction();

            // Update the model
            $model->update($request->all());

            // Execute custom logic if provided
            if ($customLogic) {
                $customLogic($request, $model);
            }

            DB::commit();
            return $this->responseService->success($model);
        } catch (Exception $e) {
            DB::rollBack();
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
