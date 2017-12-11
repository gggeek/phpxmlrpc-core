<?php

namespace PhpHttpRpc\API;

interface CharsetConverter
{
    /**
     * Convert a string to the correct representation in a target charset and format.
     *
     * Eg. one class implementing this interface would be used to encode XML text, another one to encode JSON.
     *
     * @param string $data
     * @param string $srcEncoding
     * @param string $destEncoding
     *
     * @return string
     *
     * @throws \Exception for unknown or unsupported encoding conversion
     */

    public function encodeEntities($data, $srcEncoding = '', $destEncoding = '');
}