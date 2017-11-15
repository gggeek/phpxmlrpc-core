<?php

namespace PhpHttpRpc\API;

interface Client
{
    public function __construct($uri, array $options = array());

    /**
     * Sends a request and returns the response object.
     * Note that the client will always return a Response object, even if the call fails
     *
     * @param Request $request
     *
     * @return Response
     *
     * @throws \PhpHttpRpc\API\Exception\UnsupportedOptionException if option is not supported
     */
    public function send(Request $request);

    /**
     * Retrieves the current value for any option
     * @param string $option
     *
     * @return bool|int|string
     *
     * @throws \PhpHttpRpc\API\Exception\UnsupportedOptionException if option is not supported
     */
    public function getOption($option);

    /**
     * Retrieves the list of available options
     * @return string[]
     */
    public function getOptionList();
}
