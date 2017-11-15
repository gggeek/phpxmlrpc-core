<?php

namespace PhpHttpRpc\Core;

use PhpHttpRpc\API\Request as RpcRequestInterface;
use PhpHttpRpc\API\ResponseFactory as ResponseFactoryInterface;

abstract class Request implements RpcRequestInterface, ResponseFactoryInterface
{
    protected $methodName;
    protected $params = array();
    protected $contentType = '';
    protected $responseFactoryClass = '\PhpHttpRpc\Core\ResponseFactory';

    public function __construct($methodName, array $params = array())
    {
        $this->setMethodName($methodName);
        foreach ($params as $param) {
            $this->addParam($param);
        }
    }

    protected function setMethodName($methodName)
    {
        $this->validateMethodName($methodName);
        $this->methodName = $methodName;
    }

    public function getMethodName()
    {
        return $this->methodName;
    }

    protected function addParam($param)
    {
        $this->validateParam($param);
        $this->params[] = $param;
    }

    public function getParam($i)
    {
        if (array_key_exists($i, $this->params)) {
            return $this->params[$i];
        }
        throw new \OutOfRangeException("There is no param number $i in the Request");
    }

    public function getParams()
    {
        return $this->params;
    }

    public function getNumParams()
    {
        return count($this->params);
    }

    /**
     * @param string $methodName
     * @throws \InvalidArgumentException
     */
    protected function validateMethodName($methodName)
    {
        // left for subclasses to override
    }

    /**
     * @param mixed $param
     * @throws \InvalidArgumentException
     */
    protected function validateParam($param)
    {
        // left for subclasses to override
    }

    public function getResponseFactory()
    {
        return new $this;
    }

    /**
     * The default implementation does not modify the original URI
     * @param string $uri
     * @return string
     */
    public function withHTTPUri($uri)
    {
        return $uri;
    }

    /**
     * The default is to use POST requests
     * @return string
     */
    public function getHTTPMethod()
    {
        return 'POST';
    }

    public function getHTTPHeaders()
    {
        return array(
            'Content-Type' => array($this->contentType)
        );

        // @todo if method is POST, should we add Content-Length as well? Or leave it to the Client?
    }

    abstract public function getHTTPBody();

    abstract public function parseHTTPResponse($request, $body, array $headers = array(), array $options = array());
}
