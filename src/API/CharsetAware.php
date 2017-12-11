<?php

namespace PhpHttpRpc\API;

interface CharsetAware
{
    /**
     * This method will be called by the Client before asking for the request Body
     * @param string $encoding
     */
    public function setCharsetEncoding($encoding);
}
