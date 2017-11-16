<?php

namespace PhpHttpRpc\API;

interface RequestFactory
{
    /**
     * Builds and returns an appropriate Request object from the php data.
     *
     * @param string $methodName the name of the method to invoke
     * @param mixed[]|Value[] $params array of parameters to be passed to the method
     * @param mixed[] $options the keys and values will depend on the exact type of Request that this factory will create
     * @return Request
     */
    public function createRequest($methodName, array $params = array(), array $options = array());
}
