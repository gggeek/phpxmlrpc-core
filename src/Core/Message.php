<?php

namespace PhpHttpRpc\Core;

use PhpHttpRpc\API\CharsetAware;
use PhpHttpRpc\API\CharsetConverter;

abstract class Message implements CharsetAware
{
    protected $contentType = '';
    protected $charset = '';

    /** @var CharsetConverter $charsetConverter */
    protected $charsetConverter;

    public function setCharsetEncoding($encoding)
    {
        $this->charset = $encoding;
    }

    public function setCharsetConverter(CharsetConverter $charsetConverter)
    {
        $this->charsetConverter = $charsetConverter;
    }
}