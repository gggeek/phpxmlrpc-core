<?php

namespace PhpHttpRpc\Helper\CharsetConverter;

use PhpHttpRpc\API\CharsetConverter;

/**
 * The core character set converter class can juggle between UTF-8, latin-1, us-ascii and, if enabled, cp-1252
 */
abstract class Base implements CharsetConverter
{
    // tables used for transcoding different charsets
    protected $always_encode_Entities = array("in" => array(), "out" => array());
    protected $xml_iso88591_Entities = array("in" => array(), "out" => array());
    protected $xml_iso88591_utf8 = array("in" => array(), "out" => array());

    /// @todo add to iso table the characters from cp_1252 range, i.e. 128 to 159?
    /// These will NOT be present in true ISO-8859-1, but will save the unwary
    /// windows user from sending junk (though no luck when receiving them...)
    protected $xml_cp1252_Entities = array();

    protected $charsetSupersets = array(
        'US-ASCII' => array('ISO-8859-1', 'ISO-8859-2', 'ISO-8859-3', 'ISO-8859-4',
            'ISO-8859-5', 'ISO-8859-6', 'ISO-8859-7', 'ISO-8859-8',
            'ISO-8859-9', 'ISO-8859-10', 'ISO-8859-11', 'ISO-8859-12',
            'ISO-8859-13', 'ISO-8859-14', 'ISO-8859-15', 'UTF-8',
            'EUC-JP', 'EUC-', 'EUC-KR', 'EUC-CN',),
    );

    protected static $instances = array();

    // Used for those characters which we encode using a generic scheme.
    // Will be used as format string for a sprintf call, with as input the integer number corresponding to the utf8 code point
    protected $encodedEntityFormat = '[codepoint %d]'; // left in to ease debugging

    /**
     * This class and its subclasses are singleton for performance reasons.
     * NB: there will be one instance created for each typeof subclass
     *
     * @return static
     */
    public static function instance()
    {
        $className = get_called_class();
        if (!isset(self::$instances[$className])) {
            self::$instances[$className] = new static();
        }

        return self::$instances[$className];
    }

    /**
     * Convert a string to the correct representation in a target charset and format.
     *
     * To help correct communication of non-ascii chars inside strings, regardless of the charset used when sending
     * requests, parsing them, sending responses and parsing responses, an option is to convert all non-ascii chars
     * present in the message into their equivalent 'charset entity'.
     * Charset entities enumerated this way are independent of the charset encoding used to transmit them, and all
     * XML parsers are bound to understand them.
     * The same holds true for JSON, which allows \u + utf8 code_point representation.
     * Note that if we are not sending a charset encoding mime type along with http headers, we are bound by RFC 3023 to
     * emit strict us-ascii.
     *
     * @todo do a bit of basic benchmarking (strtr vs. str_replace)
     * @todo make usage of iconv() or recode_string() or mb_string() where available
     *
     * @param string $data
     * @param string $srcEncoding
     * @param string $destEncoding
     *
     * @return string
     *
     * @throws \Exception for unknown or unsupported encoding conversion
     */
    public function encodeEntities($data, $srcEncoding = '', $destEncoding = '')
    {
        $conversion = strtoupper($srcEncoding . '_' . $destEncoding);
        switch ($conversion) {
            case 'ISO-8859-1_':
            case 'ISO-8859-1_US-ASCII':
                $escapedData = str_replace($this->always_encode_Entities['in'], $this->always_encode_Entities['out'], $data);
                $escapedData = str_replace($this->xml_iso88591_Entities['in'], $this->xml_iso88591_Entities['out'], $escapedData);
                break;

            case 'ISO-8859-1_UTF-8':
                $escapedData = str_replace($this->always_encode_Entities['in'], $this->always_encode_Entities['out'], $data);
                $escapedData = utf8_encode($escapedData);
                break;

            case 'ISO-8859-1_ISO-8859-1':
            case 'US-ASCII_US-ASCII':
            case 'US-ASCII_UTF-8':
            case 'US-ASCII_':
            case 'US-ASCII_ISO-8859-1':
            case 'UTF-8_UTF-8':
            case 'CP1252_CP1252':
                $escapedData = str_replace($this->always_encode_Entities['in'], $this->always_encode_Entities['out'], $data);
                break;

            case 'UTF-8_':
            case 'UTF-8_US-ASCII':
            case 'UTF-8_ISO-8859-1':
                // NB: this will choke on invalid UTF-8, going most likely beyond EOF
                $escapedData = '';
                // be kind to users creating string xmlrpc values out of different php types
                $data = (string)$data;
                $ns = strlen($data);
                for ($nn = 0; $nn < $ns; $nn++) {
                    $ch = $data[$nn];
                    $ii = ord($ch);
                    // 7 bits: 0bbbbbbb (127)
                    if ($ii < 128) {
                        /// @todo shall we replace this with a (supposedly) faster str_replace?
                        /*switch ($ii) {
                            case 34:
                                $escapedData .= '&quot;';
                                break;
                            case 38:
                                $escapedData .= '&amp;';
                                break;
                            case 39:
                                $escapedData .= '&apos;';
                                break;
                            case 60:
                                $escapedData .= '&lt;';
                                break;
                            case 62:
                                $escapedData .= '&gt;';
                                break;
                            default:
                                $escapedData .= $ch;
                        }*/ // switch
                        $escapedData .= str_replace($this->always_encode_Entities['in'], $this->always_encode_Entities['out'], $ch);
                    } // 11 bits: 110bbbbb 10bbbbbb (2047)
                    elseif ($ii >> 5 == 6) {
                        $b1 = ($ii & 31);
                        $ii = ord($data[$nn + 1]);
                        $b2 = ($ii & 63);
                        $ii = ($b1 * 64) + $b2;
                        $ent = sprintf($this->encodedEntityFormat, $ii);
                        $escapedData .= $ent;
                        $nn += 1;
                    } // 16 bits: 1110bbbb 10bbbbbb 10bbbbbb
                    elseif ($ii >> 4 == 14) {
                        $b1 = ($ii & 15);
                        $ii = ord($data[$nn + 1]);
                        $b2 = ($ii & 63);
                        $ii = ord($data[$nn + 2]);
                        $b3 = ($ii & 63);
                        $ii = ((($b1 * 64) + $b2) * 64) + $b3;
                        $ent = sprintf($this->encodedEntityFormat, $ii);
                        $escapedData .= $ent;
                        $nn += 2;
                    } // 21 bits: 11110bbb 10bbbbbb 10bbbbbb 10bbbbbb
                    elseif ($ii >> 3 == 30) {
                        $b1 = ($ii & 7);
                        $ii = ord($data[$nn + 1]);
                        $b2 = ($ii & 63);
                        $ii = ord($data[$nn + 2]);
                        $b3 = ($ii & 63);
                        $ii = ord($data[$nn + 3]);
                        $b4 = ($ii & 63);
                        $ii = ((((($b1 * 64) + $b2) * 64) + $b3) * 64) + $b4;
                        $ent = sprintf($this->encodedEntityFormat, $ii);
                        $escapedData .= $ent;
                        $nn += 3;
                    }
                }

                // when converting to latin-1, do not be so eager with using entities for characters 160-255
                if ($conversion == 'UTF-8_ISO-8859-1') {
                    $escapedData = str_replace(array_slice($this->xml_iso88591_Entities['out'], 32), array_slice($this->xml_iso88591_Entities['in'], 32), $escapedData);
                }
                break;

            case 'CP1252_':
            case 'CP1252_US-ASCII':
                if (!count($this->xml_cp1252_Entities)) {
                    throw new \Exception("Converting from $srcEncoding to $destEncoding: not enabled");
                }
                $escapedData = str_replace($this->always_encode_Entities['in'], $this->always_encode_Entities['out'], $data);
                $escapedData = str_replace($this->xml_iso88591_Entities['in'], $this->xml_iso88591_Entities['out'], $escapedData);
                $escapedData = str_replace($this->xml_cp1252_Entities['in'], $this->xml_cp1252_Entities['out'], $escapedData);
                break;
            case 'CP1252_UTF-8':
                if (!count($this->xml_cp1252_Entities)) {
                    throw new \Exception("Converting from $srcEncoding to $destEncoding: not enabled");
                }
                $escapedData = str_replace($this->always_encode_Entities['in'], $this->always_encode_Entities['out'], $data);
                /// @todo we could use real UTF8 chars here instead of xml entities... (note that utf_8 encode all alone will NOT convert them)
                $escapedData = str_replace($this->xml_cp1252_Entities['in'], $this->xml_cp1252_Entities['out'], $escapedData);
                $escapedData = utf8_encode($escapedData);
                break;
            case 'CP1252_ISO-8859-1':
                if (!count($this->xml_cp1252_Entities)) {
                    throw new \Exception("Converting from $srcEncoding to $destEncoding: not enabled");
                }
                $escapedData = str_replace($this->always_encode_Entities['in'], $this->always_encode_Entities['out'], $data);
                // we might as well replace all funky chars with a '?' here, but we are kind and leave it to the receiving application layer to decide what to do with these weird entities...
                $escapedData = str_replace($this->xml_cp1252_Entities['in'], $this->xml_cp1252_Entities['out'], $escapedData);
                break;

            default:
                //$escapedData = '';
                //error_log('XML-RPC: ' . __METHOD__ . ": Converting from $srcEncoding to $destEncoding: not supported...");
                throw new \Exception("Converting from $srcEncoding to $destEncoding: not supported");
        }

        return $escapedData;
    }

    /**
     * Checks if a given charset encoding is present in a list of encodings or
     * if it is a valid subset of any encoding in the list.
     *
     * @param string $encoding charset to be tested
     * @param string|array $validList comma separated list of valid charsets (or array of charsets)
     *
     * @return bool
     */
    public function isValidCharset($encoding, $validList)
    {
        if (is_string($validList)) {
            $validList = explode(',', $validList);
        }
        if (@in_array(strtoupper($encoding), $validList)) {
            return true;
        } else {
            if (array_key_exists($encoding, $this->charsetSupersets)) {
                foreach ($validList as $allowed) {
                    if (in_array($allowed, $this->charsetSupersets[$encoding])) {
                        return true;
                    }
                }
            }

            return false;
        }
    }
}
