<?php

namespace PhpHttpRpc\API\Exception;

use PhpHttpRpc\API\Fault;

/**
 * Can be thrown by code that is run as part of the server-side execution of RPC calls
 */
class RpcFaultException extends \Exception implements Fault
{
    /**
     * @return int
     */
    public function faultCode()
    {
        return $this->getCode();
    }

    /**
     * @return string
     */
    public function faultString()
    {
        return $this->getMessage();
    }
}
