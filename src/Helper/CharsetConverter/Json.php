<?php

namespace PhpHttpRpc\Helper\CharsetConverter;

/// @todo
class Json extends Base
{
    protected $encodedEntityFormat = '\\u%04x';

    protected function __construct()
    {
        $this->always_encode_Entities = array(
            'in' => array( '\\',   '"',   '/',   "\n",  "\r",  "\t"),
            'out' => array('\\\\', '\\"', '\\/', '\\n', '\\r', '\\t'), // could also add: backspace and formfeed
        );

        for ($i = 0; $i < 32; $i++) {
            $this->xml_iso88591_Entities["in"][] = chr($i);
            $this->xml_iso88591_Entities["out"][] = sprintf($this->encodedEntityFormat, $i);
        }

        for ($i = 160; $i < 256; $i++) {
            $this->xml_iso88591_Entities["in"][] = chr($i);
            $this->xml_iso88591_Entities["out"][] = sprintf($this->encodedEntityFormat, $i);
        }
    }

    /**
     * Enables the conversion of cp1252 to UTF8/LATIN1/ASCII.
     * This is kept as separate functionality to improve memory consumption and execution speed
     */
    public function enableCP1252Conversion()
    {
        $this->xml_cp1252_Entities = array('in' => array(), 'out' => array(
            '\\u20AC;', '?',        '\\u201A;', '\\u0192;',
            '\\u201E;', '\\u2026;', '\\u2020;', '\\u2021;',
            '\\u02C6;', '\\u2030;', '\\u0160;', '\\u2039;',
            '\\u0152;', '?',        '\\u017D;', '?',
            '?',        '\\u2018;', '\\u2019;', '\\u201C;',
            '\\u201D;', '\\u2022;', '\\u2013;', '\\u2014;',
            '\\u02DC;', '\\u2122;', '\\u0161;', '\\u203A;',
            '\\u0153;', '?',        '\\u017E;', '\\u0178;'
        ));

        for ($i = 128; $i < 160; $i++)
        {
            $this->xml_cp1252_Entities['in'][] = chr($i);
        }
    }
}
