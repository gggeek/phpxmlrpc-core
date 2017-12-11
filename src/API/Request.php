<?php

namespace PhpHttpRpc\API;

interface Request
{
    /**
     * @param string $methodName the name of the method to invoke
     * @param mixed[]|Value[] $params array of parameters to be passed to the method
     */
    public function __construct($methodName, array $params = array());

    /**
     * Gets the method to be invoked.
     *
     * @return string the method that will be invoked
     */
    public function getMethodName();

    /**
     * Returns the nth parameter in the request. The index zero-based.
     *
     * @param integer $i the index of the parameter to fetch (zero based)
     *
     * @return Value the i-th parameter.
     *
     * @throws \OutOfRangeException
     */
    public function getParam($i);

    /**
     * Returns all the parameters in the request.
     *
     * @return Value[] keys are integers, starting at 0
     */
    public function getParams();

    /**
     * Returns the number of parameters in the message.
     *
     * @return integer the number of parameters currently set
     */
    public function getNumParams();

    /**
     * Returns an instance of the correct ResponseFactory subclass for this Request
     *
     * @return ResponseFactory
     */
    public function getResponseFactory();

    /**
     * Retrieves the method of the HTTP request.
     * Will be called when this request is serialized for sending
     *
     * @return string Returns the request method.
     */
    public function getHTTPMethod();

    /**
     * Generates the URI of the HTTP request, starting from the injected one.
     * Will be called when this request is serialized for sending
     * Q: should we use UriInterface instead?
     *
     * @param string $uri
     *
     * @return string
     */
    public function withHTTPUri($uri);

    /**
     * Generates the headers of the HTTP request.
     * Will be called when this request is serialized for sending
     *
     * @return string[][] Returns an associative array of the request's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     */
    public function getHTTPHeaders();

    /**
     * Generates the body of the HTTP request.
     * Will be called when this request is serialized for sending
     * Q: should we return a (plain php) stream instead?
     *
     * @return string|null return NULL when the http request should have no body
     */
    public function getHTTPBody();
}
