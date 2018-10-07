<?php
namespace App\Logger;

use Psr\Log\LogLevel as PsrLogLevel;

class LogLevel extends PsrLogLevel
{
    const TRACE = 'trace';

    const FLAG_NONE      = 0;
    const FLAG_EMERGENCY = 1;
    const FLAG_ALERT     = 2;
    const FLAG_CRITICAL  = 4;
    const FLAG_ERROR     = 8;
    const FLAG_WARNING   = 16;
    const FLAG_NOTICE    = 32;
    const FLAG_INFO      = 64;
    const FLAG_DEBUG     = 128;
    const FLAG_TRACE     = 256;

    public static function getLevelFlag(string $level): int
    {
        switch ($level) {
            case self::EMERGENCY:
                return self::FLAG_EMERGENCY;

            case self::ALERT:
                return self::FLAG_ALERT;

            case self::CRITICAL:
                return self::FLAG_CRITICAL;

            case self::ERROR:
                return self::FLAG_ERROR;

            case self::WARNING:
                return self::FLAG_WARNING;

            case self::NOTICE:
                return self::FLAG_NOTICE;

            case self::INFO:
                return self::FLAG_INFO;

            case self::DEBUG:
                return self::FLAG_DEBUG;

            case self::TRACE:
                return self::FLAG_TRACE;

            default:
                return self::FLAG_NONE;
        }
    }
}
