<?php

namespace PhpHttpRpc\API;

interface Fault
{
    /**
     * @return int
     */
    public function faultCode();

    /**
     * @return string
     */
    public function faultString();
}
