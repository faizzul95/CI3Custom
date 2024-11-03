<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

// Define HTTP status codes as constants for better code readability
const HTTP_STATUS_CODES = [
    200 => 'OK',
    201 => 'Created',
    202 => 'Accepted',
    204 => 'No Content',
    205 => 'Reset Content',
    206 => 'Partial Content',
    300 => 'Multiple Choices',
    301 => 'Moved Permanently',
    302 => 'Found',
    303 => 'See Other',
    304 => 'Not Modified',
    307 => 'Temporary Redirect',
    308 => 'Permanent Redirect',
    400 => 'Bad Request',
    401 => 'Unauthorized',
    402 => 'Payment Required',
    403 => 'Forbidden',
    404 => 'Not Found',
    405 => 'Method Not Allowed',
    406 => 'Not Acceptable',
    407 => 'Proxy Authentication Required',
    408 => 'Request Timeout',
    409 => 'Conflict',
    410 => 'Gone',
    411 => 'Length Required',
    412 => 'Precondition Failed',
    413 => 'Payload Too Large',
    414 => 'URI Too Long',
    415 => 'Unsupported Media Type',
    416 => 'Range Not Satisfiable',
    417 => 'Expectation Failed',
    422 => 'Unprocessable Entity',
    423 => 'Locked',
    424 => 'Failed Dependency',
    429 => 'Too Many Requests',
    500 => 'Internal Server Error',
    501 => 'Not Implemented',
    502 => 'Bad Gateway',
    503 => 'Service Unavailable',
    504 => 'Gateway Timeout',
    505 => 'HTTP Version Not Supported',
];

/**
 * Send a JSON response with an optional HTTP status code.
 *
 * @param mixed $data          The data to be encoded and sent as JSON.
 * @param int   $response_code The HTTP status code to be sent (default is 200 OK).
 * @param bool  $encode        The HTTP status code to be sent (default is 200 OK).
 */
if (!function_exists('jsonResponse')) {
    function jsonResponse($data, $response_code = 200)
    {
        // Check if $data is an array and has a 'code' key
        if (is_array($data) && (isset($data['code']))) {
            $response_code = (int) isset($data['code']) ? $data['code'] : $response_code;
        }

        // Check if the provided HTTP status code is valid, otherwise default to 400 Bad Request
        if (!array_key_exists($response_code, HTTP_STATUS_CODES)) {
            $response_code = 400;
        }

        // Set the HTTP response code and content type
        http_response_code($response_code);

        // Set the Content-Type header to indicate JSON response
        header('Content-Type: application/json');

        // Encode the data as JSON with pretty printing for readability
        echo json_encode($data, JSON_PRETTY_PRINT);

        // Terminate the script
        exit;
    }
}

/**
 * Handles validation error responses.
 *
 * This function sets the HTTP response code to 422, sends a JSON response with
 * error details, and exits the script.
 */
if (!function_exists('validationErrorReturn')) {
    function validationErrorReturn()
    {
        // Create an array containing error code, message, ID, and empty data array
        $response = [
            'code' => 422,
            'message' => validation_errors(),
            'id' => null,
            'data' => [],
        ];

        jsonResponse($response, 422);
    }
}

/**
 * Generate a message based on a given code.
 *
 * @param int    $code The code to determine the message.
 * @param string $text The default text to include in the message.
 * @return string Returns a formatted message string.
 */
if (!function_exists('message')) {
    function message($code, $text = NULL)
    {
        if (isSuccess($code)) {
            return empty($text) ? 'Save successfully' : ucfirst($text);
        } else {
            return empty($text) ? 'Please consult the system administrator' : ucfirst($text);
        }
    }
}

/**
 * Check if the given HTTP response code indicates success.
 *
 * @param mixed $code The HTTP response code to check (default: 200).
 * @return bool True if the response code is a success status, false otherwise.
 */
if (!function_exists('isSuccess')) {
    function isSuccess($code = 200)
    {
        // Define an array of HTTP status codes that represent success access.
        $successStatus = [200, 201, 302];

        // Convert the input response code to an integer if it's a string.
        $code = is_string($code) ? (int) $code : $code;

        // Check if the code is in the list of success status codes.
        return in_array($code, $successStatus);
    }
}

/**
 * Check if the given HTTP response code indicates an error.
 *
 * @param mixed $code The HTTP response code to check (default: 400).
 * @return bool True if the response code is an error status, false otherwise.
 */
if (!function_exists('isError')) {
    function isError($code = 400)
    {
        // Define an array of HTTP status codes that represent error access.
        $errorStatus = [204, 301, 302, 400, 404, 413, 414, 415, 422, 429, 500];

        // Convert the input response code to an integer if it's a string.
        $code = is_string($code) ? (int) $code : $code;

        // Check if the code is in the list of error status codes.
        return in_array($code, $errorStatus);
    }
}

/**
 * Check if the given HTTP response code represents an unauthorized access.
 *
 * @param mixed $code The HTTP response code to check, can be an integer or string.
 * @return bool True if the response code is 401 or 403, indicating unauthorized access; otherwise, false.
 */
if (!function_exists('isUnauthorized')) {
    function isUnauthorized($code = 401)
    {
        // Define an array of HTTP status codes that represent unauthorized access.
        $unauthorizedStatusCodes = [401, 403];

        // Convert the input response code to an integer if it's a string.
        $code = is_string($code) ? (int) $code : $code;

        // Check if the code is in the list of unauthorized status codes.
        return in_array($code, $unauthorizedStatusCodes);
    }
}
