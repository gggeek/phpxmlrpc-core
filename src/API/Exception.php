<?php

namespace PhpHttpRpc\API;

class Exception extends \Exception
{
    const UNKNOWN_METHOD = 'unknown_method';
    const INVALID_RETURN = 'invalid_return';
    const INCORRECT_PARAMS = 'incorrect_params';
    const INTROSPECT_UNKNOWN = 'introspect_unknown';
    const HTTP_ERROR = 'http_error';
    const NO_DATA = 'no_data';
    const NO_SSL = 'no_ssl';
    const CURL_FAIL = 'curl_fail';
    const INVALID_REQUEST = 'invalid_request';
    const NO_CURL = 'no_curl';
    const SERVER_ERROR = 'server_error';
    const MULTICALL_ERROR = 'multicall_error';
    const MULTICALL_NOTSTRUCT = 'multicall_notstruct';
    const MULTICALL_NOMETHOD = 'multicall_nomethod';
    const MULTICALL_NOTSTRING = 'multicall_notstring';
    const MULTICALL_RECURSION = 'multicall_recursion';
    const MULTICALL_NOPARAMS = 'multicall_noparams';
    const MULTICALL_NOTARRAY = 'multicall_notarray';

    const CANNOT_DECOMPRESS = 'cannot_decompress';
    const DECOMPRESS_FAIL = 'decompress_fail';
    const DECHUNK_FAIL = 'dechunk_fail';
    const SERVER_CANNOT_DECOMPRESS = 'server_cannot_decompress';
    const SERVER_DECOMPRESS_FAIL = 'server_decompress_fail';

    public static $xmlrpcerr = array(
        'unknown_method' => 1,
        'invalid_return' => 2,
        'incorrect_params' => 3,
        'introspect_unknown' => 4,
        'http_error' => 5,
        'no_data' => 6,
        'no_ssl' => 7,
        'curl_fail' => 8,
        'invalid_request' => 15,
        'no_curl' => 16,
        'server_error' => 17,
        'multicall_error' => 18,
        'multicall_notstruct' => 9,
        'multicall_nomethod' => 10,
        'multicall_notstring' => 11,
        'multicall_recursion' => 12,
        'multicall_noparams' => 13,
        'multicall_notarray' => 14,

        'cannot_decompress' => 103,
        'decompress_fail' => 104,
        'dechunk_fail' => 105,
        'server_cannot_decompress' => 106,
        'server_decompress_fail' => 107,
    );

    static public $xmlrpcstr = array(
        'unknown_method' => 'Unknown method',
        'invalid_return' => 'Invalid response payload (you can use the setDebug method to allow analysis of the response)',
        'incorrect_params' => 'Incorrect parameters passed to method',
        'introspect_unknown' => "Can't introspect: method unknown",
        'http_error' => "Didn't receive 200 OK from remote server",
        'no_data' => 'No data received from server',
        'no_ssl' => 'No SSL support compiled in',
        'curl_fail' => 'CURL error',
        'invalid_request' => 'Invalid request payload',
        'no_curl' => 'No CURL support compiled in',
        'server_error' => 'Internal server error',
        'multicall_error' => 'Received from server invalid multicall response',
        'multicall_notstruct' => 'system.multicall expected struct',
        'multicall_nomethod' => 'Missing methodName',
        'multicall_notstring' => 'methodName is not a string',
        'multicall_recursion' => 'Recursive system.multicall forbidden',
        'multicall_noparams' => 'Missing params',
        'multicall_notarray' => 'params is not an array',

        'cannot_decompress' => 'Received from server compressed HTTP and cannot decompress',
        'decompress_fail' => 'Received from server invalid compressed HTTP',
        'dechunk_fail' => 'Received from server invalid chunked HTTP',
        'server_cannot_decompress' => 'Received from client compressed HTTP request and cannot decompress',
        'server_decompress_fail' => 'Received from client invalid compressed HTTP request',
    );

    /**
     * @param string $errorType one of the constants
     * @param string $appendString if non null will be appended to the predefined error message for the given error type
     * @param \Exception $previous
     *
     * @return Exception
     */
    public static function instance($errorType, $appendString = '', $previous = null) {
        if ($appendString != '') {
            $appendString = ' ' . $appendString;
        }
        return new static(static::$xmlrpcerr[$errorType], static::$xmlrpcstr[$errorType] . $appendString);
    }
}
