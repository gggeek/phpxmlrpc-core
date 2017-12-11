<?php

namespace PhpHttpRpc\Helper\CharsetConverter;

class XML extends Base
{
    protected $encodedEntityFormat = '&#%d;';

    protected function __construct()
    {
        $this->always_encode_Entities = array(
            'in' => array( '&',     '"',      "'",      '<',    '>'),
            'out' => array('&amp;', '&quot;', '&apos;', '&lt;', '&gt;'),
        );

        for ($i = 0; $i < 32; $i++) {
            $this->xml_iso88591_Entities["in"][] = chr($i);
            $this->xml_iso88591_Entities["out"][] = "&#{$i};";
        }

        for ($i = 160; $i < 256; $i++) {
            $this->xml_iso88591_Entities["in"][] = chr($i);
            $this->xml_iso88591_Entities["out"][] = "&#{$i};";
        }
    }

    /**
     * Enables the conversion of cp1252 to UTF8/LATIN1/ASCII.
     * This is kept as separate functionality to improve memory consumption and execution speed
     */
    public function enableCP1252Conversion()
    {
        $this->xml_cp1252_Entities = array('in' => array(), 'out' => array(
            '&#x20AC;', '?',        '&#x201A;', '&#x0192;',
            '&#x201E;', '&#x2026;', '&#x2020;', '&#x2021;',
            '&#x02C6;', '&#x2030;', '&#x0160;', '&#x2039;',
            '&#x0152;', '?',        '&#x017D;', '?',
            '?',        '&#x2018;', '&#x2019;', '&#x201C;',
            '&#x201D;', '&#x2022;', '&#x2013;', '&#x2014;',
            '&#x02DC;', '&#x2122;', '&#x0161;', '&#x203A;',
            '&#x0153;', '?',        '&#x017E;', '&#x0178;'
        ));

        for ($i = 128; $i < 160; $i++)
        {
            $this->xml_cp1252_Entities['in'][] = chr($i);
        }
    }
}
