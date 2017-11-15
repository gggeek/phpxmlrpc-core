<?php

namespace PhpHttpRpc\API;

interface Value
{
    public function serialize(array $options = array());
}
