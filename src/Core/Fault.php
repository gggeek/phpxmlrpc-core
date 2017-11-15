<?php

namespace PhpHttpRpc\Core;

use PhpHttpRpc\API\Fault as FaultInterface;

class Fault implements FaultInterface
{
    protected $faultCode;
    protected $faultString;

    public function __construct($faultCode, $faultString = "")
    {
        $this->faultCode = $faultCode;
        $this->faultString = $faultString;
    }

    public function faultCode()
    {
        return $this->faultCode;
    }

    public function faultString()
    {
        return $this->faultString;
    }

    /**
     * This is called when an error is converted to plain text
     */
    public function __toString()
    {
        return $this->faultCode . ': "' . $this->faultString . '"';
    }
}
