<?php

namespace PhpHttpRpc\Core;

use PhpHttpRpc\API\Response as RpcResponseInterface;
use PhpHttpRpc\API\Fault;

abstract class Response extends Message implements RpcResponseInterface
{
    protected $faultCode = 0;
    protected $faultString = '';
    protected $value;

    public function __construct($value)
    {
        if ($value instanceof \Exception) {
            return $this->setFault($value->getCode(), $value->getMessage());
        }

        if ($value instanceof Fault) {
            return $this->setFault($value->faultCode(), $value->faultString());
        }

        /// @todo any other well-known 'fault' classes that we should handle ?

        return $this->setNonFault($value);
    }

    public function faultCode()
    {
        return $this->faultCode;
    }

    public function faultString()
    {
        return $this->faultString;
    }

    public function value()
    {
        return $this->value;
    }

    protected function setFault($faultCode, $faultString)
    {
        ///  @todo we should add a validateFault() method call here. Eg. we could check that $faultCode !== 0 ...

        $this->faultCode = $faultCode;
        $this->faultString = $faultString;
        $this->value = null;

        return $this;
    }

    protected function setNonFault($value)
    {
        ///  @todo we should add a validateValue() method call here. Eg. we could check that $value is of good type

        $this->faultCode = 0;
        $this->faultString = '';
        $this->value = $value;

        return $this;
    }

    /**
     * Will be called when this response is serialized for sending
     *
     * @return string[][] Returns an associative array of the response's headers. Each
     *     key MUST be a header name, and each value MUST be an array of strings
     *     for that header.
     */
    abstract public function getHTTPHeaders();

    /**
     * Will be called when this response is serialized for sending
     * Q: should we return a (plain php) stream instead?
     *
     * @return string
     */
    abstract public function getHTTPBody();
}
