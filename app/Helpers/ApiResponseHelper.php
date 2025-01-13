<?php

if(!function_exists('apiResponse')){
    /*
        200 OK - Request succeeded.
        201 Created - Resource successfully created.
        400 Bad Request - Invalid input or parameters.
        401 Unauthorized - Authentication failure.
        404 Not Found - Resource not found.
        500 Internal Server Error - Server-side issue.
    */

    /**
     *  Create consistent API Response
     *  @param bool $success
     *  @param int|null $status
     *  @param string $message
     *  @param mixed|null $data
     *  @param mixed $meta
     *  @return \Illuminate\Http\JsonResponse
     */
    
    function apiResponse($success, $status=200, $message, $data=null, $meta=[]){
        return response()->json([
            'success' => $success,
            'status'  => $status,
            'message' => $message,
            'data'    => $data, // can contain error ressponse
            'meta'    => array_merge($meta),
        ], $status);
    }
}

?>