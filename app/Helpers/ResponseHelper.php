<?php

namespace App\Helpers;

class ResponseHelper{
    /*
        200 OK - Request succeeded.
        201 Created - Resource successfully created.
        400 Bad Request - Invalid input or parameters.
        401 Unauthorized - Authentication failure.
        404 Not Found - Resource not found.
        500 Internal Server Error - Server-side issue.
    */

    /**
     * Format a successful API response.
     *
     * @param mixed $data
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public static function success($data, $message = 'Request successful', $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'status'  => $statusCode,
            'message' => $message,
            'data'    => $data,
            'meta'    => [
            ],
        ], $statusCode);
    }

    /**
     * Format an error API response.
     *
     * @param string $message
     * @param array|null $errors
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    public static function error($message = 'An error occurred', $errors = null, $statusCode = 400)
    {
        return response()->json([
            'success' => false,
            'status'  => $statusCode,
            'message' => $message,
            'data'    => null,
            'meta'    => [
                'errors' => $errors,
            ],
        ], $statusCode);
    }


}



?>