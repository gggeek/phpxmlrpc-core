<?php

namespace PhpHttpRpc\Helper\Logger;

use Psr\Log\AbstractLogger;

/**
 * Echoes to stadard output all types of messages, taking care of escaping them as required for HTML when not in console mode.
 * NB: if the encoding of the message is not known or wrong, and we are working in web mode, there is no guarantee
 *     of 100% accuracy, which kind of defeats the purpose of debugging
 *
 * @todo find a more appropriate name? It could be HTMLLogger, or LegacyLogger...
 */
class EchoLogger extends AbstractLogger
{
    /**
     * @param string $level
     * @param string $message
     * @param array $context
     *
     * @todo decide what key to use in the $context array for 'encoding'
     * @todo add more types of escaping of output? fe. javascript or plain-text even when in non-cli mode...
     * @todo for maximum compatibility with legacy phpxmlrpc, we could use error_log calls for anything error or above...
     */
    protected function log($level, $message, array $context = array())
    {
        $encoding = isset($context['encoding']) ? $context['encoding'] : null;

        // US-ASCII is a warning for PHP and a fatal for HHVM
        if ($encoding == 'US-ASCII') {
            $encoding = 'UTF-8';
        }

        if (PHP_SAPI != 'cli') {
            $flags = ENT_COMPAT | ENT_HTML401 | ENT_SUBSTITUTE;
            if ($encoding != null) {
                echo "<PRE>\n" . htmlentities($message, $flags, $encoding) . "\n</PRE>";
            } else {
                echo "<PRE>\n" . htmlentities($message, $flags) . "\n</PRE>";
            }
        } else {
            echo "\n$message\n";
        }

        // let the user see this now in case there's a timeout later...
        flush();
    }
}
