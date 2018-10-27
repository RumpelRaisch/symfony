<?php
namespace App\Logger\Abstracts;

use \Exception;
use App\Logger\Interfaces\LoggerInterface;
use App\Logger\LogLevel;

/**
 * [AbstractLogger description]
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
    private $flags = LogLevel::FLAG_NONE;

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
     * {@inheritdoc}
     */
    public function debugR($message, array $context = [])
    {
        $this->debug(print_r($message, true), $context);
    }

    /**
     * {@inheritdoc}
     */
    public function debugDump($message, array $context = [])
    {
        $this->debug($this->getVarDump($message), $context);
    }

    /**
     * {@inheritdoc}
     */
    public function debugException(Exception $ex, array $context = [])
    {
        $this->debug("Exception:\n" . $this->getExceptionData($ex), $context);
    }

    /**
     * {@inheritdoc}
     */
    public function trace($message, array $context = [])
    {
        $this->log(LogLevel::TRACE, $message, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function traceR($message, array $context = [])
    {
        $this->trace(print_r($message, true), $context);
    }

    /**
     * {@inheritdoc}
     */
    public function traceDump($message, array $context = [])
    {
        $this->trace($this->getVarDump($message), $context);
    }

    /**
     * {@inheritdoc}
     */
    public function traceException(Exception $ex, array $context = [])
    {
        $this->trace("Exception:\n" . $this->getExceptionData($ex), $context);
    }

    /**
     * {@inheritdoc}
     */
    abstract public function log($level, $message, array $context = []);

    /**
     * Interpolates context values into the message placeholders.
     *
     * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md#12-message
     *
     * @param string $message
     * @param array  $context
     *
     * @return string
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
     * @return string [description]
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

        $pre  = '[' . sprintf("%'=9s", strtoupper($level)) . '] ';
        $pre .= date('Y-m-d H:i:s') . ' | ';

        $message  = preg_replace('/\r\n/', "\n", $message);
        $message  = preg_replace('/\r/', "\n", $message);
        $message  = preg_replace('/\t/', ' ', $message);
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
     * @param $data
     *
     * @return string
     */
    protected function getVarDump($data): string
    {
        $eol = preg_quote(PHP_EOL);

        ob_start();

        var_dump($data);

        return preg_replace("/\]=>{$eol} +/", '] => ', ob_get_clean());
    }

    /**
     * @param Exception $ex
     * @param int       $i
     *
     * @return string
     */
    protected function getExceptionData(Exception $ex, int $i = 1): string
    {
        $response = "#{$i} - {$ex->getMessage()} [{$ex->getFile()}:{$ex->getLine()}]";

        if (null !== ($prev = $ex->getPrevious())) {
            $response .= "\n" . $this->getExceptionData($prev, ++$i);
        }

        return $response;
    }

    /**
     * [addLogLevel description]
     *
     * @param string ...$levels [description]
     *
     * @return self
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

        return $this;
    }

    /**
     * [removeLogLevel description]
     *
     * @param string ...$levels [description]
     *
     * @return self
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

        return $this;
    }

    /**
     * [resetLogLevel description]
     *
     * @return self
     */
    public function resetLogLevel()
    {
        $this->flags = LogLevel::FLAG_NONE;

        return $this;
    }

    /**
     * [setFlag description]
     *
     * @param int $flag [description]
     *
     * @return self
     */
    protected function setFlag(int $flag)
    {
        $this->flags |= $flag;

        return $this;
    }

    /**
     * [removeFlag description]
     *
     * @param int $flag [description]
     *
     * @return self
     */
    protected function removeFlag(int $flag)
    {
        $this->flags &= ~$flag;

        return $this;
    }

    /**
     * [issetFlag description]
     *
     * @param int $flag [description]
     *
     * @return bool [description]
     */
    protected function issetFlag(int $flag): bool
    {
        return (($this->flags & $flag) === $flag);
    }

    /**
     * [getFlags description]
     *
     * @return int current bit flags
     */
    public function getFlags(): int
    {
        return $this->flags;
    }

    /**
     * @param int $flags
     *
     * @return self
     */
    protected function setFlags(int $flags)
    {
        $this->flags = $flags;

        return $this;
    }
}
