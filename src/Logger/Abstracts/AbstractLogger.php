<?php
namespace App\Logger\Abstracts;

use Exception;
use App\Flag\Interfaces\FlagInterface;
use App\Flag\Traits\FlagTrait;
use App\Logger\Interfaces\LoggerInterface;
use App\Logger\LogLevel;

/**
 * [AbstractLogger description]
 *
 * @see https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-3-logger-interface.md
 * @see \Psr\Log\LoggerInterface
 */
abstract class AbstractLogger implements LoggerInterface, FlagInterface
{
    use FlagTrait;

    /**
     * {@inheritdoc}
     */
    public function emergency($message, array $context = [])
    {
        $this->log(LogLevel::EMERGENCY, self::getMessage($message), $context);
    }

    /**
     * {@inheritdoc}
     */
    public function alert($message, array $context = [])
    {
        $this->log(LogLevel::ALERT, self::getMessage($message), $context);
    }

    /**
     * {@inheritdoc}
     */
    public function critical($message, array $context = [])
    {
        $this->log(LogLevel::CRITICAL, self::getMessage($message), $context);
    }

    /**
     * {@inheritdoc}
     */
    public function error($message, array $context = [])
    {
        $this->log(LogLevel::ERROR, self::getMessage($message), $context);
    }

    /**
     * {@inheritdoc}
     */
    public function warning($message, array $context = [])
    {
        $this->log(LogLevel::WARNING, self::getMessage($message), $context);
    }

    /**
     * {@inheritdoc}
     */
    public function notice($message, array $context = [])
    {
        $this->log(LogLevel::NOTICE, self::getMessage($message), $context);
    }

    /**
     * {@inheritdoc}
     */
    public function info($message, array $context = [])
    {
        $this->log(LogLevel::INFO, self::getMessage($message), $context);
    }

    /**
     * {@inheritdoc}
     */
    public function debug($message, array $context = [])
    {
        $this->log(LogLevel::DEBUG, self::getMessage($message), $context);
    }

    /**
     * {@inheritdoc}
     */
    public function debugDump($message, array $context = [])
    {
        $this->debug(self::getVarDump($message), $context);
    }

    /**
     * {@inheritdoc}
     */
    public function trace($message, array $context = [])
    {
        $this->log(LogLevel::TRACE, self::getMessage($message), $context);
    }

    /**
     * {@inheritdoc}
     */
    public function traceDump($message, array $context = [])
    {
        $this->trace(self::getVarDump($message), $context);
    }

    /**
     * @param mixed $message
     *
     * @return string
     */
    public static function getMessage($message): string
    {
        if (true === is_string($message)) {
            return $message;
        }

        if ($message instanceof Exception) {
            return self::getExceptionData($message);
        }

        if (true === is_array($message) || true === is_object($message)) {
            return print_r($message, true);
        }

        if (true === is_numeric($message)) {
            return (string) $message;
        }

        if (null === $message) {
            return 'null';
        }

        if (true === is_bool($message)) {
            return true === $message ? 'true' : 'false';
        }

        if (true === is_callable($message)) {
            return self::getMessage($message());
        }

        return $message;
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
     * @param array $context
     *
     * @return string
     */
    public static function interpolate(string $message, array $context = []): string
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
     * @param array $context
     *
     * @return string
     */
    public static function beautify(
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
    public static function getVarDump($data): string
    {
        $eol = preg_quote(PHP_EOL);

        ob_start();

        var_dump($data);

        return preg_replace("/\]=>{$eol} +/", '] => ', ob_get_clean());
    }

    /**
     * @param Exception $ex
     * @param int $i
     *
     * @return string
     */
    public static function getExceptionData(Exception $ex, int $i = 1): string
    {
        $response = '';

        if (1 === $i) {
            $response = "Exception:\n";
        }

        $response .= "#{$i} - {$ex->getMessage()} ";
        $response .= "[{$ex->getFile()}:{$ex->getLine()}]";

        if (0 < $ex->getCode()) {
            $response .= " [Code {$ex->getCode()}]";
        }

        if (null !== ($prev = $ex->getPrevious())) {
            $response .= "\n" . self::getExceptionData($prev, ++$i);
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
