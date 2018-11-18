<?php
namespace App\Logger\Types;

use App\Logger\Abstracts\AbstractLogger;

/**
 * Class ConsoleLogger
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class ConsoleLogger extends AbstractLogger
{
    /**
     * ConsoleLogger constructor.
     *
     * @param string ...$levels
     */
    public function __construct(string ...$levels)
    {
        if (0 < count($levels)) {
            $this->addLogLevel(...$levels);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function log($level, $message, array $context = [])
    {
        if (true === $this->shouldLog($level)) {
            $message = self::interpolate($message, $context);
            $message = self::beautify($level, $message, $context);

            echo $message;
        }
    }
}
