<?php
namespace App\Logger\Types;

use App\Logger\Abstracts\AbstractLogger;
use App\Logger\LogLevel;

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
     * @param string ...$levels [description]
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
        $levelFlag = LogLevel::getLevelFlag($level);

        if (
            LogLevel::FLAG_NONE === $levelFlag ||
            false === $this->issetFlag($levelFlag)
        ) {
            return;
        }

        $message = $this->interpolate($message, $context);
        $message = $this->beautify($level, $message, $context);

        echo $message;
    }
}
