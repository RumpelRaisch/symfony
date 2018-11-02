<?php
namespace App\Logger\Types;

use \Exception;
use App\Logger\Abstracts\AbstractLogger;

/**
 * Class FileLogger
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class FileLogger extends AbstractLogger
{
    /**
     * @var string
     */
    private $file = null;

    /**
     * @var integer
     */
    private $maxFileSize = 51200;

    /**
     * FileLogger constructor.
     *
     * @param string $file
     * @param string ...$levels
     *
     * @throws Exception
     */
    public function __construct(string $file, string ...$levels)
    {
        try {
            $this->file = $file;

            file_put_contents($this->file, '', FILE_APPEND);
        } catch (Exception $ex) {
            $this->file = null;

            throw new Exception('Logfile not writable.', 0, $ex);
        }

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
            $message = $this->interpolate($message, $context);
            $message = $this->beautify($level, $message, $context);

            $this->checkFileSize();

            file_put_contents($this->file, $message, FILE_APPEND);
        }
    }

    /**
     * @return FileLogger
     */
    protected function checkFileSize(): FileLogger
    {
        if (filesize($this->file) > $this->maxFileSize) {
            $newFile = preg_replace(
                '/\/([^\/]*\.)*([^\/\.]+)$/',
                '/backup/${1}' . date('YmdHis.') . '$2',
                $this->file
            );

            $backupDir = pathinfo($newFile)['dirname'];

            if (false === is_dir($backupDir)) {
                mkdir($backupDir, 0664, true);
            }

            rename($this->file, $newFile);
        }

        return $this;
    }

    /**
     * Set the value of maxFileSize property
     *
     * @param integer $maxFileSize
     *
     * @return FileLogger
     */
    public function setMaxFileSize(int $maxFileSize): FileLogger
    {
        $this->maxFileSize = $maxFileSize;

        return $this;
    }

    /**
     * Get the value of file property
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Get the path of the log file
     *
     * @return string
     */
    public function getPath()
    {
        return pathinfo($this->file, PATHINFO_DIRNAME);
    }
}
