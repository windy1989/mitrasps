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
     *  @param mixed $meta (must be array)
     *  @return \Illuminate\Http\JsonResponse
     */
    
    function apiResponse($success, $status, $message, $data=null, $meta=[]){
        $status = $status ?? ($success ? 200 : 400);

        return response()->json([
            'success' => $success,
            'status'  => $status,
            'message' => $message,
            'data'    => $data,                 // error response if validation error
            'meta'    => array_merge($meta),    // pagination, current_page, per_page, total_pages, total data 
        ], $status);
    }
}

?>