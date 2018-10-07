<?php
namespace App\Logger\Abstracts;

use App\Logger\LogLevel;
use Psr\Log\LoggerInterface;

use \Exception;

/**
 * [SimpleFileLogger description]
 *
 * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
 * @see Psr\Log\LoggerInterface
 */
abstract class AbstractLogger implements LoggerInterface
{
    /**
     * [private description]
     *
     * @var integer
     */
    private $flags = 0;

    /**
     * {@inheritdoc}
     */
    public function emergency($message, array $context = [])
    {
        $this->log(LogLevel::EMERGENCY, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function alert($message, array $context = [])
    {
        $this->log(LogLevel::ALERT, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function critical($message, array $context = [])
    {
        $this->log(LogLevel::CRITICAL, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, array $context = [])
    {
        $this->log(LogLevel::ERROR, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, array $context = [])
    {
        $this->log(LogLevel::WARNING, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function notice($message, array $context = [])
    {
        $this->log(LogLevel::NOTICE, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function info($message, array $context = [])
    {
        $this->log(LogLevel::INFO, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message, array $context = [])
    {
        $this->log(LogLevel::DEBUG, $message, $context);
    }

    /**
     * [debugR description]
     *
     * @param mixed $message [description]
     * @param array $context [description]
     */
    public function debugR($message, array $context = [])
    {
        $this->debug(print_r($message, true), $context);
    }

    /**
     * Detailed trace information.
     *
     * @param string $message
     * @param array  $context
     */
    public function trace($message, array $context = [])
    {
        $this->log(LogLevel::TRACE, $message, $context);
    }

    /**
     * [traceR description]
     *
     * @param mixed $message [description]
     * @param array $context [description]
     */
    public function traceR($message, array $context = [])
    {
        $this->trace(print_r($message, true), $context);
    }

    /**
     * {@inheritdoc}
     */
    abstract public function log($level, $message, array $context = []);

    /**
     * Interpolates context values into the message placeholders.
     *
     * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md#12-message
     */
    protected function interpolate(string $message, array $context = [])
    {
        // build a replacement array with braces around the context keys
        $replace = [];
        foreach ($context as $key => $val) {
            // check that the value can be casted to string
            if (!is_array($val) && (!is_object($val) || method_exists($val, '__toString'))) {
                $replace['{' . $key . '}'] = $val;
            }
        }

        // interpolate replacement values into the message and return
        return strtr($message, $replace);
    }

    /**
     * [beautify description]
     *
     * @param string $level   [description]
     * @param string $message [description]
     * @param array  $context [description]
     *
     * @return string          [description]
     */
    protected function beautify(
        string $level,
        string $message,
        array  $context = []
    ): string {
        $area = 'APP';

        if (false === empty($context['__AREA__'])) {
            $area = $context['__AREA__'];
        }

        $pre  = '[' . sprintf("%' 9s", $level) . '] ';
        $pre .=  date('Y-m-d H:i:s') . ' | ';

        $message  = preg_replace('/\r\n/', "\n", $message);
        $message  = preg_replace('/\r/', "\n", $message);
        $message  = preg_replace('/\t/', " ", $message);
        $message .= ' (' . $area . ')';
        $lines    = explode("\n", $message);
        $message  = array_shift($lines) . "\n";

        foreach ($lines as $line) {
            $line     = '    ' . $line;
            $line     = rtrim($line);
            $message .= $line . "\n";
        }

        return $pre . $message;
    }

    /**
     * [addLogLevel description]
     *
     * @param string $levels [description]
     */
    public function addLogLevel(string ...$levels)
    {
        foreach ($levels as $level) {
            $levelFlag = LogLevel::getLevelFlag($level);

            if (LogLevel::FLAG_NONE === $levelFlag) {
                continue;
            }

            $this->setFlag($levelFlag);
        }
    }

    /**
     * [removeLogLevel description]
     *
     * @param string $levels [description]
     */
    public function removeLogLevel(string ...$levels)
    {
        foreach ($levels as $level) {
            $levelFlag = LogLevel::getLevelFlag($level);

            if (LogLevel::FLAG_NONE === $levelFlag) {
                continue;
            }

            $this->removeFlag($levelFlag);
        }
    }

    /**
     * [resetLogLevel description]
     */
    public function resetLogLevel()
    {
        $this->flags = LogLevel::FLAG_NONE;
    }

    /**
     * [setFlag description]
     *
     * @param int $flag [description]
     */
    protected function setFlag(int $flag)
    {
        $this->flags |= $flag;
    }

    /**
     * [removeFlag description]
     *
     * @param int    $flag [description]
     */
    protected function removeFlag(int $flag)
    {
        $this->flags &= ~$flag;
    }

    /**
     * [issetFlag description]
     *
     * @param int  $flag [description]
     *
     * @return bool       [description]
     */
    protected function issetFlag(int $flag): bool
    {
        return (($this->flags & $flag) === $flag);
    }

    /**
     * [getFlags description]
     *
     * @return [type] [description]
     */
    public function getFlags()
    {
        return $this->flags;
    }
}
