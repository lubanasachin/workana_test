<?php

namespace app\domain\chat;

/**
 * Response mgmt service 
 * @author: Sachin Lubana
*/

class Response {

    /**
     * Send json response (success & failure both)
     * @params
     *  boolean $isError 
     *  int $responseCode HTTP response code
     *  String $response
    */
    public static function sendResponse($isError, $responseCode, $response) {
        header_remove('Set-Cookie');
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($responseCode);
        if ($isError) echo json_encode(['error' => $isError, 'message' => $response]);
        else echo json_encode($response);
        exit();
    }

}
