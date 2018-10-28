<?php
namespace App\Helper;

use \RuntimeException;
use \UnexpectedValueException;
use App\Kernel;
use Symfony\Component\Cache\Simple\FilesystemCache;

class CacheHelper
{
    /** @var string */
    public const DEFAULT = 'default';

    /** @var string */
    public const SIDEBAR = 'sidebar';

    /** @var array */
    private static $caches = [];

    /**
     * @param null|Kernel $kernel
     *
     * @throws RuntimeException
     *
     * @return FilesystemCache
     */
    public static function getDefault(Kernel $kernel = null): FilesystemCache
    {
        $kernel = self::getKernel($kernel);

        if (true === empty(self::$caches[self::DEFAULT])) {
            self::$caches[self::DEFAULT] = new FilesystemCache(
                self::DEFAULT,
                0,
                $kernel->getAppCacheDir()
            );
        }

        return self::$caches[self::DEFAULT];
    }

    /**
     * @param null|Kernel $kernel
     *
     * @throws RuntimeException
     *
     * @return FilesystemCache
     */
    public static function getSidebar(Kernel $kernel = null): FilesystemCache
    {
        $kernel = self::getKernel($kernel);

        if (true === empty(self::$caches[self::SIDEBAR])) {
            self::$caches[self::SIDEBAR] = new FilesystemCache(
                self::SIDEBAR,
                0,
                $kernel->getAppCacheDir() . '/views'
            );
        }

        return self::$caches[self::SIDEBAR];
    }

    /**
     * @param string      $name
     * @param null|Kernel $kernel
     *
     * @throws UnexpectedValueException
     * @throws RuntimeException
     *
     * @return mixed
     */
    public static function get(
        string $name   = self::DEFAULT,
        Kernel $kernel = null
    ): FilesystemCache {
        $method = 'get' . ucfirst($name);

        if (false === method_exists(self::class, $method)) {
            throw new UnexpectedValueException('Unknown Cache');
        }

        return self::{$method}($kernel);
    }

    /**
     * @return array
     */
    public static function getAvailableCahces(): array
    {
        return [
            self::DEFAULT,
            self::SIDEBAR,
        ];
    }

    /**
     * @param null|Kernel $kernel
     *
     * @throws RuntimeException
     *
     * @return Kernel
     */
    private static function getKernel(?Kernel $kernel): Kernel
    {
        if (null === $kernel) {
            $kernel = Kernel::getInstance();
        }

        if (null === $kernel) {
            throw new RuntimeException(Kernel::class . ' not active');
        }

        return $kernel;
    }
}
