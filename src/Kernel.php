<?php
namespace App;

use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;
use Symfony\Component\Routing\RouteCollectionBuilder;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    const AVATAR_DEFAULT_FILE = __DIR__ . '/../public/img/avatar.default.png';
    const AVATAR_DEFAULT_MIME = 'image/png';
    const AVATAR_MALE_FILE    = __DIR__ . '/../public/img/avatar.male.png';
    const AVATAR_FEMALE_FILE  = __DIR__ . '/../public/img/avatar.female.png';
    const CONFIG_EXTS         = '.{php,xml,yaml,yml}';

    private static $instance = null;

    public function __construct(string $environment, bool $debug)
    {
        parent::__construct($environment, $debug);

        self::$instance = $this;
    }

    public static function getInstance()
    {
        return self::$instance;
    }

    public function getCacheDir()
    {
        return $this->getProjectDir().'/var/cache/'.$this->getEnvironment();
    }

    public function getAppCacheDir()
    {
        return $this->getCacheDir().'/app';
    }

    public function getTempDir()
    {
        return $this->getProjectDir().'/var/tmp/'.$this->getEnvironment();
    }

    public function getAppTempDir()
    {
        return $this->getTempDir().'/app';
    }

    public function getLogDir()
    {
        return $this->getProjectDir().'/var/log';
    }

    public function getPublicDir()
    {
        return $this->getProjectDir().'/public';
    }

    public function registerBundles()
    {
        $contents = require $this->getProjectDir().'/config/bundles.php';
        foreach ($contents as $class => $envs) {
            if (isset($envs['all']) || isset($envs[$this->environment])) {
                yield new $class();
            }
        }
    }

    protected function configureContainer(ContainerBuilder $container, LoaderInterface $loader)
    {
        $container->addResource(new FileResource($this->getProjectDir().'/config/bundles.php'));
        // Feel free to remove the "container.autowiring.strict_mode" parameter
        // if you are using symfony/dependency-injection 4.0+ as it's the default behavior
        $container->setParameter('container.autowiring.strict_mode', true);
        $container->setParameter('container.dumper.inline_class_loader', true);
        $confDir = $this->getProjectDir().'/config';

        $loader->load($confDir.'/{packages}/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{packages}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}'.self::CONFIG_EXTS, 'glob');
        $loader->load($confDir.'/{services}_'.$this->environment.self::CONFIG_EXTS, 'glob');
    }

    protected function configureRoutes(RouteCollectionBuilder $routes)
    {
        $confDir = $this->getProjectDir().'/config';

        $routes->import($confDir.'/{routes}/*'.self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir.'/{routes}/'.$this->environment.'/**/*'.self::CONFIG_EXTS, '/', 'glob');
        $routes->import($confDir.'/{routes}'.self::CONFIG_EXTS, '/', 'glob');
    }
}
