<?php

namespace PhpHttpRpc\Core;

use Psr\Http\Message\RequestInterface as HttpRequestInterface;
use Psr\Http\Message\ResponseInterface as HttpResponseInterface;
use Http\Message\RequestFactory as HttpRequestFactory;
use Http\Client\HttpClient as HttpClientInterface;
use PhpHttpRpc\HTTP\Discovery\HttpClientDiscovery;
use PhpHttpRpc\HTTP\Discovery\MessageFactoryDiscovery;
use PhpHttpRpc\API\Client as RpcClientInterface;
use PhpHttpRpc\API\Request as RpcRequestInterface;
use PhpHttpRpc\API\Response as RpcResponseInterface;
use PhpHttpRpc\API\RequestFactory as RpcRequestFactoryInterface;
use PhpHttpRpc\API\ResponseFactory as RpcResponseFactoryInterface;
use PhpHttpRpc\API\Exception\UnsupportedOptionException;

abstract class Client implements RpcClientInterface, RpcRequestFactoryInterface
{
    const USE_CURL_NEVER = 0;
    const USE_CURL_ALWAYS = 1;
    const USE_CURL_AUTO = 2;

    const EXCEPTIONS_NEVER = 0; // never throw exceptions, always return a Response
    const EXCEPTIONS_TRANSPORT = 1; // throws exceptions for all transport-layer (http) errors
    const EXCEPTIONS_RESPONSE_FORMAT = 2; // throws exceptions for all errors decoding the body of the Response
    const EXCEPTIONS_PROTOCOL = 4; // throws exceptions for in-protocol errors
    const EXCEPTIONS_ALWAYS = 7; // always throw exceptions

    protected $options = array(
        'httpVersion' => null,
        'keepAlive' => true,
        'userAgent' => null,
        'acceptedCharsetEncodings' => null,
        'timeout' => null,
        'useCURL' => self::USE_CURL_AUTO,

        'requestCompression' => null,
        'acceptedCompression' => null,

        'username' => null,
        'password' => null,
        'authType' => null,

        'proxyHost' => null,
        'proxyPort' => null,
        'proxyUsername' => null,
        'proxyPassword' => null,
        'proxyAuthType' => null,

        'SSLVersion' => null,
        'SSLVerifyHost' => null,
        'SSLVerifyPeer' => null,
        'SSLCert' => null,
        'SSLCertPass' => null,
        'SSLCACert' => null,
        'SSLCACertDir' => null,
        'SSLKey' => null,
        'SSLKeyPass' => null,

        /// @todo decide how much we want to support these 2 levels of flexibility...
        ///       maybe we can achieve them using simply different subclasses?
        'useExceptions' => self::EXCEPTIONS_ALWAYS,
        'returnType' => RpcResponseFactoryInterface::RETURN_PHP,

        'debug' => 0,
    );

    /** @var RpcRequestFactoryInterface $rpcRequestFactory */
    protected $rpcRequestFactory;
    /** @var HttpRequestFactory $httpRequestFactory */
    protected $httpRequestFactory;
    /** @var HttpClientInterface $httpClient */
    protected $httpClient;

    /**
     * Client constructor.
     * @param string $uri
     * @param array $options beside the options listed above: rpcRequestFactory, httpRequestFactory, httpClient
     */
    public function __construct($uri, array $options = array())
    {
        if (isset($options['rpcRequestFactory'])) {
            $requestFactory = $options['rpcRequestFactory'];
            unset($options['rpcRequestFactory']);
        } else {
            // we use NULL instead of $this to avoid a silly circular reference
            $requestFactory = null;
        }
        $this->setRpcRequestFactory($requestFactory);

        if (isset($options['httpRequestFactory'])) {
            $requestFactory = MessageFactoryDiscovery::find();
        } else {
            $requestFactory = $options['httpRequestFactory'];
            unset($options['httpRequestFactory']);
        }
        $this->setHTTPRequestFactory($requestFactory);

        if (isset($options['httpClient'])) {
            $client = HttpClientDiscovery::find($options);
            // We allow the httpClient to handle the 'httpResponseFactory' option without storing it locally
            // This gives us flexibility, even thought it is a small violation of architectural principles (this class
            // should not know what HttpClientDiscovery does with options...)
            if (isset($options['httpResponseFactory'])) {
                unset($options['httpResponseFactory']);
            }
        } else {
            $client = $options['httpClient'];
            unset($options['httpClient']);
        }
        $this->setHTTPClient($client);

        $this->setOptions($options);
    }

    /**
     * Returns the name of the Request class that is to be used by default
     * @return string
     */
    abstract protected function getRpcRequestClass();

    /**
     * The primary API to call the remote webservice
     *
     * @param string $methodName
     * @param array $params
     *
     * @return mixed what do we return ???
     */
    public function call($methodName, array $params = array())
    {
        $request = $this->createRequest($methodName, $params);
        $response = $this->send($request);

        /// @todo unwrap / throw exception ???

        return $response->value();
    }

    /**
     * Sends a request and returns the response object.
     * Note that the client will always return a Response object, even if the call fails
     *
     * @param RpcRequestInterface $request
     * @return RpcResponseInterface
     */
    public function send(RpcRequestInterface $request)
    {
        try {
            $httpRequest = $this->buildHttpRequest($request);
            $httpResponse = $this->httpClient->sendRequest($httpRequest);
            $rpcResponse = $this->buildRpcResponse($request, $httpResponse);
            return $rpcResponse;
        } catch(\Exception $e) {
            return $this->buildErrorResponse($e);
        }
    }

    /**
     * Even though this is public, t is designed for internal use, really
     *
     * @param string $methodName
     * @param array $params
     * @param array $options
     * @return \PhpHttpRpc\API\Request
     */
    public function createRequest($methodName, array $params = array(), array $options = array())
    {
        if ($this->rpcRequestFactory === null) {
            $requestClass = $this->getRpcRequestClass();
            return new $requestClass($methodName, $params);
        }

        return $this->rpcRequestFactory->createRequest($methodName, $params);
    }

    /**
     * Retrieves the current value for any option
     * @param string $option
     *
     * @return bool|int|string
     *
     * @throws UnsupportedOptionException if option is not supported
     */
    public function getOption($option)
    {
        if (!array_key_exists($option, $this->options) )
        {
            throw new UnsupportedOptionException("Option $option is not supported");
        }

        return $this->options[$option];
    }

    public function getOptionsList()
    {
        return array_keys($this->options);
    }

    /**
     * One-stop shop for setting all configuration options without having to write a hundred method calls
     * @param string $option
     * @param mixed $value
     *
     * @throws UnsupportedOptionException if option is not supported or valid
     */
    protected function setOption($option, $value)
    {
        if (!array_key_exists($option, $this->options) )
        {
            throw new UnsupportedOptionException("Option $option is not supported");
        }

        switch ($option) {
            default:
                $this->validateOption($option, $value);
                $this->options[$option] = $value;
        }

        // NB: if by subclassing you make this class non-immutable, you should probably re-validate (rebuild?) the chosen
        // http client here, unless you do it on send()
    }

    /**
     * @param string $option
     * @param mixed $value
     *
     * @throws UnsupportedOptionException if option is not valid
     */
    protected function validateOption($option, $value)
    {
        // left for subclasses to implement
    }

    /**
     * Set many options in one fell swoop
     *
     * @param array $options
     *
     * @throws UnsupportedOptionException if an option is not supported
     */
    protected function setOptions(array $options)
    {
        foreach($options as $name => $value)
        {
            $this->setOption($name, $value);
        }

        // NB: if by subclassing you make this class non-immutable, you should probably re-validate (rebuild?) the chosen
        // http client here, unless you do it on send()
    }

    protected function setRpcRequestFactory(RpcRequestFactoryInterface $requestFactory)
    {
        $this->rpcRequestFactory = $requestFactory;
    }

    protected function setHTTPRequestFactory(HttpRequestFactory $requestFactory)
    {
        $this->httpRequestFactory = $requestFactory;
    }

    protected function setHTTPClient(HttpClientInterface $client)
    {
        $this->httpClient = $client;
    }

    /**
     * @param RpcRequestInterface $request
     *
     * @return HttpRequestInterface
     */
    protected function buildHttpRequest(RpcRequestInterface $request)
    {
        $httpRequest = $this->httpRequestFactory->createRequest(
            $request->getHTTPMethod(),
            $request->withHTTPUri($this->getUri()),
            $this->getHTTPRequestHeaders($request->getHTTPHeaders()),
            $this->getHTTPRequestBody($request->getHTTPBody()),
            $this->getHTTPRequestProtocolVersion()
        );

        return $httpRequest;
    }

    protected function getUri()
    {
        /// @todo
    }

    protected function getHTTPRequestHeaders(array $headers = array())
    {
        /// @todo add all our own headers
        return $headers;
    }

    protected function getHTTPRequestBody($body)
    {
        /// @todo compress if needed
        return $body;
    }

    protected function getHTTPRequestProtocolVersion()
    {
        /// @todo allow options to modify this
        return '1.1';
    }

    /**
     * @param RpcRequestInterface $request
     * @param HttpResponseInterface $httpResponse
     *
     * @return RpcResponseInterface
     */
    protected function buildRpcResponse(RpcRequestInterface $request, HttpResponseInterface $httpResponse)
    {
        $headers = $httpResponse->getHeaders();
        $body = (string)$httpResponse->getBody();
        return $request->getResponseFactory()->parseHTTPResponse(
            $request,
            $body,
            $headers,
            array(
                'debug' => $this->getOption('debug'),
                'returnType' => $this->getOption('returnType'),
                'useExceptions' => $this->getOption('useExceptions'),
            )
        );
    }

    /**
     * @param \Exception $e
     *
     * @return RpcResponseInterface
     */
    protected function buildErrorResponse(\Exception $e)
    {
        /// @todo
    }
}
