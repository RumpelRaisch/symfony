<?php
namespace App\Logger\Interfaces;

use \Exception;
use Psr\Log\LoggerInterface as PsrLoggerInterface;

/**
 * Interface LoggerInterface
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
interface LoggerInterface extends PsrLoggerInterface
{
    /**
     * Detailed debug information.
     *
     * @param string $message
     * @param array  $context
     */
    public function debugDump($message, array $context = []);

    /**
     * Detailed trace information.
     *
     * @param string $message
     * @param array  $context
     */
    public function trace($message, array $context = []);

    /**
     * Detailed trace information.
     *
     * @param string $message
     * @param array  $context
     */
    public function traceDump($message, array $context = []);
}
