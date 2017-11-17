<?php

namespace PhpHttpRpc\API\Exception;

use PhpHttpRpc\API\Fault;

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
