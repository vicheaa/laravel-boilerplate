<?php

namespace App\Http\Helpers;

class ApiResponse
{
    public static function success($data = null, $message = 'Success', $code = 200)
    {
        return response()->json([
            'success'   => true,
            'message'   => $message,
            'data'      => $data,
        ], $code);
    }

    public static function error($message = 'Error', $code = 400, $errors = null)
    {
        return response()->json([
            'success'   => false,
            'message'   => $message,
            'errors'    => $errors,
        ], $code);
    }

    public static function paginated($paginator, $code = 200)
    {
        return response()->json([
            'total'     => $paginator->total(),
            'next_page' => $paginator->nextPageUrl(),
            'prev_page' => $paginator->previousPageUrl(),
            'data'      => $paginator->items(),
        ], $code);
    }
}
