<?php

namespace PhpHttpRpc\API;

use Psr\Log\LoggerInterface;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LogLevel;

/**
 * Make it as easy as possible to have logging in all our classes
 *
 * Traits can not implement interfaces, but this implements LoggerAwareInterface and LoggerInterface
 */
trait LoggingTrait
{
    use LoggerAwareTrait;

    /**
     * @return LoggerInterface|null
     */
    protected function getLogger()
    {
        return $this->logger;
    }

    /**
     * System is unusable.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    protected function emergency($message, array $context = array())
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * Action must be taken immediately.
     *
     * Example: Entire website down, database unavailable, etc. This should
     * trigger the SMS alerts and wake you up.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    protected function alert($message, array $context = array())
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * Critical conditions.
     *
     * Example: Application component unavailable, unexpected exception.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    protected function critical($message, array $context = array())
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * Runtime errors that do not require immediate action but should typically
     * be logged and monitored.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    protected function error($message, array $context = array())
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * Exceptional occurrences that are not errors.
     *
     * Example: Use of deprecated APIs, poor use of an API, undesirable things
     * that are not necessarily wrong.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    protected function warning($message, array $context = array())
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * Normal but significant events.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    protected function notice($message, array $context = array())
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * Interesting events.
     *
     * Example: User logs in, SQL logs.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    protected function info($message, array $context = array())
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     *
     * @return void
     */
    protected function debug($message, array $context = array())
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * Logs with an arbitrary level.
     *
     * @param string $level @see LogLevel
     * @param string $message
     * @param array $context
     *
     * @return void
     */
    protected function log($level, $message, array $context = array())
    {
        if ($this->logger == null) {
            return;
        }
        switch($level) {
            case LogLevel::EMERGENCY:
                $this->logger->emergency($message, $context);
               break;
            case LogLevel::ALERT:
                $this->logger->alert($message, $context);
                break;
            case LogLevel::CRITICAL:
                $this->logger->critical($message, $context);
                break;
            case LogLevel::ERROR:
                $this->logger->error($message, $context);
                break;
            case LogLevel::WARNING:
                $this->logger->warning($message, $context);
                break;
            case LogLevel::NOTICE:
                $this->logger->notice($message, $context);
                break;
            case LogLevel::INFO:
                $this->logger->info($message, $context);
                break;
            case LogLevel::DEBUG:
                $this->logger->debug($message, $context);
                break;
        }
    }
}
