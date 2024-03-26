<?php
class ResponseManager
{
    public static function sendResponse($statusCode, $data, $error)
    {
        header("Content-Type: application/json");
        http_response_code($statusCode);
        $resp = ['data' => $data, 'error' => $error];
        return json_encode($resp, JSON_NUMERIC_CHECK);
    }
}
