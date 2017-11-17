<?php

namespace PhpHttpRpc\API;

interface Client
{
    public function __construct($uri, array $options = array());

    /**
     * The primary API to call the remote webservice
     *
     * @param string $methodName
     * @param array $params
     *
     * @return mixed what do we return ???
     *
     * @throws \PhpHttpRpc\API\Exception\RpcFaultException
     */
    public function call($methodName, array $params = array());

    /**
     * Sends a request and returns the response object.
     * @todo TO BE DECIDED: will the client will always return a Response object, even if the call fails?
     *
     * @param Request $request
     *
     * @return Response
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
    public function getOptionsList();
}
