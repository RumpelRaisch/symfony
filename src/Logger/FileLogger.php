<?php
namespace App\Logger;

use \Exception;

/**
 * [FileLogger description]
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class FileLogger extends Abstracts\AbstractLogger
{
    /**
     * [private description]
     *
     * @var string
     */
    private $file = null;

    /**
     * [private description]
     *
     * @var integer
     */
    private $maxFileSize = 51200;

    /**
     * [__construct description]
     *
     * @param string $file      [description]
     * @param string ...$levels [description]
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

            throw new Exception('Logfile not writeable.', 0, $ex);
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
        $levelFlag = LogLevel::getLevelFlag($level);

        if (
            LogLevel::FLAG_NONE === $levelFlag ||
            false === $this->issetFlag($levelFlag)
        ) {
            return;
        }

        $message = $this->interpolate($message, $context);
        $message = $this->beautify($level, $message, $context);

        $this->checkFileSize();

        file_put_contents($this->file, $message, FILE_APPEND);
    }

    /**
     * [checkFileSize description]
     *
     * @return FileLogger
     */
    protected function checkFileSize(): FileLogger
    {
        if (filesize($this->file) > $this->maxFileSize) {
            $pathInfo = pathinfo($this->file);
            // $newFile  = $pathInfo['dirname']
            //     . date('/Ymd.His.')
            //     . $pathInfo['basename'];
            $newFile = preg_replace(
                '/\/([^\/]*\.)*([^\/\.]+)$/',
                '/${1}' . date('YmdHis.') . '$2',
                $this->file
            );

            rename($this->file, $newFile);
        }

        return $this;
    }

    /**
     * Set the value of [private description]
     *
     * @param  integer $maxFileSize
     *
     * @return FileLogger
     */
    public function setMaxFileSize(int $maxFileSize): FileLogger
    {
        $this->maxFileSize = $maxFileSize;

        return $this;
    }

    /**
     * Get the value of [private description]
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
