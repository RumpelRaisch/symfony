<?php
namespace App\Logger;

use \Exception;
use App\Logger\Abstracts\AbstractLogger;
use App\Logger\Types\ConsoleLogger;
use App\Logger\Types\FileLogger;

/**
 * Class LoggerContainer
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class LoggerContainer extends AbstractLogger
{
    /** @var array */
    private $loggers = [];

    /** @var null|LoggerContainer */
    private static $instance = null;

    /**
     * LoggerContainer constructor.
     */
    private function __construct()
    {
        // singleton
    }

    /**
     * @return static
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new static();
        }

        return self::$instance;
    }

    /**
     * @param AbstractLogger $logger
     * @param null|string    $name
     *
     * @return AbstractLogger
     */
    protected function addLogger(
        AbstractLogger $logger,
        string         $name = null
    ): AbstractLogger {
        $logger->setFlags($this->getFlags());

        if (null !== $name) {
            $this->loggers[$name] = $logger;
        } else {
            $this->loggers[] = $logger;
        }

        return $logger;
    }

    /**
     * @param string      $file
     * @param null|string $name
     *
     * @throws Exception
     *
     * @return AbstractLogger
     */
    public function addFileLogger(string $file, string $name = null): AbstractLogger
    {
        return $this->addLogger(new FileLogger($file), $name);
    }

    /**
     * @param null|string $name
     *
     * @return AbstractLogger
     */
    public function addConsoleLogger(string $name = null): AbstractLogger
    {
        return $this->addLogger(new ConsoleLogger(), $name);
    }

    /**
     * @param string $method
     * @param array  $args
     */
    protected function callLoggers(string $method, array $args)
    {
        if (false === empty($this->loggers)) {
            foreach ($this->loggers as $logger) {
                call_user_func_array([$logger, $method], $args);
            }
        }
    }

    /** {@inheritdoc} */
    public function emergency($message, array $context = [])
    {
        $this->callLoggers('emergency', [$message, $context]);
    }

    /** {@inheritdoc} */
    public function alert($message, array $context = [])
    {
        $this->callLoggers('alert', [$message, $context]);
    }

    /** {@inheritdoc} */
    public function critical($message, array $context = [])
    {
        $this->callLoggers('critical', [$message, $context]);
    }

    /** {@inheritdoc} */
    public function error($message, array $context = [])
    {
        $this->callLoggers('error', [$message, $context]);
    }

    /** {@inheritdoc} */
    public function warning($message, array $context = [])
    {
        $this->callLoggers('warning', [$message, $context]);
    }

    /** {@inheritdoc} */
    public function notice($message, array $context = [])
    {
        $this->callLoggers('notice', [$message, $context]);
    }

    /** {@inheritdoc} */
    public function info($message, array $context = [])
    {
        $this->callLoggers('info', [$message, $context]);
    }

    /** {@inheritdoc} */
    public function debug($message, array $context = [])
    {
        $this->callLoggers('debug', [$message, $context]);
    }

    /** {@inheritdoc} */
    public function debugR($message, array $context = [])
    {
        $this->callLoggers('debugR', [$message, $context]);
    }

    /** {@inheritdoc} */
    public function debugDump($message, array $context = [])
    {
        $this->callLoggers('debugDump', [$message, $context]);
    }

    /** {@inheritdoc} */
    public function debugException(Exception $ex, array $context = [])
    {
        $this->callLoggers('debugException', [$ex, $context]);
    }

    /** {@inheritdoc} */
    public function trace($message, array $context = [])
    {
        $this->callLoggers('trace', [$message, $context]);
    }

    /** {@inheritdoc} */
    public function traceR($message, array $context = [])
    {
        $this->callLoggers('traceR', [$message, $context]);
    }

    /** {@inheritdoc} */
    public function traceDump($message, array $context = [])
    {
        $this->callLoggers('traceDump', [$message, $context]);
    }

    /** {@inheritdoc} */
    public function traceException(Exception $ex, array $context = [])
    {
        $this->callLoggers('traceException', [$ex, $context]);
    }

    /** {@inheritdoc} */
    public function log($level, $message, array $context = [])
    {
        $this->callLoggers('log', [$level, $message, $context]);
    }

    /** {@inheritdoc} */
    public function addLogLevel(string ...$levels)
    {
        parent::addLogLevel(...$levels);

        $this->callLoggers('setFlags', [$this->getFlags()]);

        return $this;
    }

    /** {@inheritdoc} */
    public function removeLogLevel(string ...$levels)
    {
        parent::removeLogLevel(...$levels);

        $this->callLoggers('setFlags', [$this->getFlags()]);

        return $this;
    }

    /** {@inheritdoc} */
    public function resetLogLevel()
    {
        parent::resetLogLevel();

        $this->callLoggers('setFlags', [$this->getFlags()]);

        return $this;
    }

    /** {@inheritdoc} */
    public function unsetLogLevel()
    {
        parent::unsetFlags();

        $this->callLoggers('unsetFlags', []);

        return $this;
    }

    /** {@inheritdoc} */
    public function setFlag(?int $flag, string $name = 'default')
    {
        // ignore $name, this is only for global (default) flags
        parent::setFlag($flag);

        $this->callLoggers('setFlags', [$this->getFlags()]);

        return $this;
    }

    /** {@inheritdoc} */
    public function removeFlag(?int $flag, string $name = 'default')
    {
        // ignore $name, this is only for global (default) flags
        parent::removeFlag($flag);

        $this->callLoggers('setFlags', [$this->getFlags()]);

        return $this;
    }

    /**
     * @return array
     */
    public function getLoggers(): array
    {
        return $this->loggers;
    }
}
