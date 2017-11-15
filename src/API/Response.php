<?php

namespace PhpHttpRpc\API;

interface Response
{
    /**
     * @param Value|Fault|\Exception|string|mixed $value the value to initialize the Response with
     */
    public function __construct($value);

    /**
     * Returns the error code of the response.
     *
     * @return integer the error code of this response (0 for not-error responses)
     */
    public function faultCode();

    /**
     * Returns the error code of the response.
     *
     * @return string the error string of this response ('' for not-error responses)
     */
    public function faultString();

    /**
     * Returns the value received by the server. If the Response's faultCode is non-zero then the value returned by this
     * method should not be used (it may not even be an object).
     *
     * q: why not return a Fault object when the Response faultCode is non-0 ?
     *
     * @return Value|string|mixed the Value object returned by the server. Might be an xml/json/... string or plain php value
     *                            depending on the convention adopted when creating the Response
     */
    public function value();

    /**
     * Will be called when this response is serialized for sending
     *
     * @return string[][] Returns an associative array of the response's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     */
    public function getHTTPHeaders();

    /**
     * Will be called when this response is serialized for sending
     * Q: should we return a (plain php) stream instead?
     *
     * @return string
     */
    public function getHTTPBody();
}
