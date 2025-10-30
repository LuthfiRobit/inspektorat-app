<?php

namespace App\Services;

class ResponseService
{
    const STATUS_SUCCESS = 200;
    const STATUS_VALIDATION_ERROR = 422;
    const STATUS_ERROR = 500;
    const STATUS_NOT_FOUND = 404;

    /**
     * Return a JSON response with a specific status code and message.
     *
     * @param mixed $data
     * @param int $statusCode
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function response($data = null, $statusCode = 200, $message = '')
    {
        return response()->json([
            'status' => $statusCode,
            'message' => $message,
            'data' => $data,
        ], $statusCode);
    }

    /**
     * Return a successful response with data.
     *
     * @param mixed $data
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function success($data, $message = 'Request successful')
    {
        return $this->response($data, self::STATUS_SUCCESS, $message);
    }

    /**
     * Return a validation error response.
     *
     * @param array $errors
     * @return \Illuminate\Http\JsonResponse
     */
    public function validationError($errors)
    {
        return $this->response($errors, self::STATUS_VALIDATION_ERROR, 'Validation failed');
    }

    /**
     * Return an error response for any unexpected issues.
     *
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public function error($message = 'An error occurred', $statusCode = self::STATUS_ERROR)
    {
        return $this->response(null, $statusCode, $message);
    }

    /**
     * Return a paginated response.
     *
     * @param mixed $data
     * @param string $message
     * @return \Illuminate\Http\JsonResponse
     */
    public function paginated($data, $message = 'Data retrieved successfully')
    {
        return response()->json([
            'status' => self::STATUS_SUCCESS,
            'message' => $message,
            'data' => $data->items(),
            'pagination' => [
                'total' => $data->total(),
                'per_page' => $data->perPage(),
                'current_page' => $data->currentPage(),
                'last_page' => $data->lastPage(),
            ],
        ], self::STATUS_SUCCESS);
    }
}
