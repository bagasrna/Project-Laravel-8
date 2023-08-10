<?php

namespace App\Libraries;

class ResponseBase
{
    /**
     * Handle response success
     * @param array $data
     * @return json
     */
    public static function success($message, $data = null, $status = 'success',  $code = 200)
    {
        $response = [];
        $response['code'] = $code;
        $response['status'] = isset($status) && $status ? $status : 'success';
        $response['message'] = isset($message) && $message ? $message : null;
        $response['data'] = isset($data) && $data ? $data : null;

        return response()->json($response)->header('Content-Language', 'id');
    }

     /**
     * Handle response success
     * @param string $message
     * @param integer $code
     * @return json
     */
    public static function error($message, $code = 400)
    {
        $response = [];
        $response['code'] = $code <= 0 ? 400 : $code;
        $response['status'] = 'error';
        $response['message'] = $message;

        return response()->json($response, $code)->header('Content-Language', 'id');
    }
}