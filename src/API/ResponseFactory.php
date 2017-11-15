<?php

namespace PhpHttpRpc\API;

interface ResponseFactory
{
    const RETURN_VALUE = 0; // Build responses that return objects of type Value (subclasess thereof)
    const RETURN_PHP = 1; // Build responses that return plain php values
    const RETURN_RAW = 2; // Build responses that return the HTTP response body as string

    /**
     * Builds and returns an appropriate Response object from the http data.
     *
     * @param Request $request
     * @param string $body
     * @param array $headers
     * @param array $options Allowed keys: 'debug', 'returnType', 'useExceptions'
     * @return Response
     */
    public function parseHTTPResponse($request, $body, array $headers = array(), array $options = array());
}
