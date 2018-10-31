<?php
namespace App\Logger\Abstracts;

use \Exception;
use App\Flag\Interfaces\FlagInterface;
use App\Flag\Traits\FlagTrait;
use App\Logger\Interfaces\LoggerInterface;
use App\Logger\LogLevel;

/**
 * [AbstractLogger description]
 *
 * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
 * @see Psr\Log\LoggerInterface
 */
abstract class AbstractLogger implements LoggerInterface, FlagInterface
{
    use FlagTrait;

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
     * @param string $level
     * @param string $message
     * @param array  $context
     *
     * @return string
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
     * @param string ...$levels
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
     * @param string ...$levels
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
     * @return self
     */
    public function resetLogLevel()
    {
        $this->setFlag(LogLevel::FLAG_NONE);

        return $this;
    }

    /**
     * @return self
     */
    public function unsetLogLevel()
    {
        $this->unsetFlags();

        return $this;
    }

    /**
     * @param string ...$levels
     *
     * @return self
     */
    public function addInstanceLogLevel(string ...$levels)
    {
        foreach ($levels as $level) {
            $levelFlag = LogLevel::getLevelFlag($level);

            if (LogLevel::FLAG_NONE === $levelFlag) {
                continue;
            }

            $this->setFlag($levelFlag, 'instance');
        }

        return $this;
    }

    /**
     * @param string ...$levels
     *
     * @return self
     */
    public function removeInstanceLogLevel(string ...$levels)
    {
        foreach ($levels as $level) {
            $levelFlag = LogLevel::getLevelFlag($level);

            if (LogLevel::FLAG_NONE === $levelFlag) {
                continue;
            }

            $this->removeFlag($levelFlag, 'instance');
        }

        return $this;
    }

    /**
     * @return self
     */
    public function resetInstanceLogLevel()
    {
        $this->setFlag(LogLevel::FLAG_NONE, 'instance');

        return $this;
    }

    /**
     * @return self
     */
    public function unsetInstanceLogLevel()
    {
        $this->unsetFlags('instance');

        return $this;
    }

    protected function shouldLog($level)
    {
        $levelFlag = LogLevel::getLevelFlag($level);
        $flags     = $this->issetFlags('instance') ? 'instance' : 'default';

        if (
            LogLevel::FLAG_NONE === $levelFlag ||
            false === $this->issetFlag($levelFlag, $flags)
        ) {
            return false;
        }

        return true;
    }
}
