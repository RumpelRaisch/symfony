<?php
namespace App\Controller;

use \Exception;
use App\Annotations\Sidebar;
use App\Controller\Abstracts\AbstractController;
use App\Helper\CacheHelper;
use App\Logger\LoggerContainer;
use App\Logger\LogLevel;
use RecursiveIteratorIterator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class AdminController
 *
 * @IsGranted("ROLE_ADMIN")
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class AdminController extends AbstractController
{
    public const CONTROLLER_NAME = 'admin';
    public const SESSION_ADMIN   = self::SESSION_ROOT . '/' . self::CONTROLLER_NAME;

    private $context = [];

    /**
     * AdminController constructor
     *
     * @param KernelInterface $kernel
     *
     * @throws Exception
     */
    public function __construct(KernelInterface $kernel)
    {
        parent::__construct($kernel);

        $this->setDefaultSession();
        $this->context['__AREA__'] = 'AdminController';

        if (null === $this->getSession()->get(self::SESSION_ADMIN, null)) {
            $this->getSession()->set(self::SESSION_ADMIN, []);
        }
    }

    /**
     * @return Response
     *
     * @Route("/admin/debug", name="admin.debug")
     * @Sidebar(name="Admin", icon="tim-icons icon-badge")
     * @Sidebar(name="Geeky Stuff", icon="tim-icons icon-atom", position=1, parent="Admin")
     */
    public function debugView(): Response
    {
        LoggerContainer::getInstance()
            ->trace(self::CONTROLLER_NAME . '.debug', $this->context);

        $test = [];
        $str  = '200 Test RS';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str    = 'Test RS';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str    = '200';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str    = ' Test RS';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str    = '200 ';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str    = ' ';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];
        $str    = '';
        preg_match('/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/', $str, $matches);
        $test[] = [$str, $matches];

        $levels         = [];
        $log            = [];
        $log['env']     = getenv('LOG_LEVEL');
        $log['explode'] = explode(',', $log['env']);

        foreach ($log['explode'] as $i => $logLevel) {
            try {
                $value = constant(LogLevel::class . '::' . $logLevel);

                $levels[] = $value;

                $log['explode'][$i] = [
                    $logLevel,
                    $value,
                ];
            } catch (Exception $ex) {
                // 42
            }
        }

        /** @var \App\Entity\User $user */
        // $user = $this->getUser();

        return $this->renderWithConfig(
            self::CONTROLLER_NAME . '/debug.html.twig',
            [
                'test' => [
                    $log,
                    $levels,
                    LoggerContainer::getInstance()->getFlags(),
                    $this->get('kernel')->getLogDir(),
                    $test,
                ],
            ],
            self::CONTROLLER_NAME,
            'Geeky Stuff',
            [self::CONTROLLER_NAME . '.debug']
        );
    }

    /**
     * @param string $file
     *
     * @return Response
     *
     * @Route(
     *      "/admin/log/{file}",
     *      name="admin.log",
     *      defaults={"file"=""},
     *      requirements={"file"=".*"}
     * )
     * @Sidebar(name="Log Files", icon="tim-icons icon-notes", position=2, parent="Admin")
     */
    public function logView(string $file = ''): Response
    {
        LoggerContainer::getInstance()
            ->trace(self::CONTROLLER_NAME . ".log - file: '{$file}'", $this->context);

        $file = strtr($file, ['%' => '']);
        $dd   = '/\.\.\//';

        while (true === (bool) preg_match($dd, $file)) {
            $file = preg_replace($dd, '', $file);
        }

        $strtr       = ['\\' => '/'];
        $logDir      = strtr($this->get('kernel')->getLogDir(), $strtr);
        $logDirInfo  = [];
        $dirIterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator(
                $logDir,
                RecursiveDirectoryIterator::SKIP_DOTS
            )
        );

        foreach ($dirIterator as $fileInfo) {
            if (false === $fileInfo->isFile()) {
                continue;
            }

            $relPathname = strtr(
                strtr($fileInfo->getPathname(), $strtr),
                [$logDir . '/' => '']
            );

            $logDirInfo[] = [
                'name' => $relPathname,
                'time' => date('Y-m-d H:i:s', $fileInfo->getMTime()),
                'size' => $fileInfo->getSize() . ' bytes',
            ];
        }

        $logContent = null;

        if ('' !== $file) {
            try {
                $logContent = file_get_contents("{$logDir}/$file");
            } catch (Exception $ex) {
                $logContent = 'Unable to get log file content.';
            }
        }

        return $this->renderWithConfig(
            self::CONTROLLER_NAME . '/log.html.twig',
            [
                'file'       => $file,
                'logDirInfo' => $logDirInfo,
                'log'        => $logContent,
            ],
            self::CONTROLLER_NAME,
            'Log Files',
            [self::CONTROLLER_NAME . '.log']
        );
    }

    /**
     * @param Request $request
     * @param string  $type
     * @param string  $action
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * @return Response
     *
     * @IsGranted("ROLE_RAISCH")
     * @Route("/admin/cache", name="admin.cache")
     * @Route(
     *     "/admin/cache/{type}/{action}",
     *     name="admin.cache",
     *     requirements={
     *          "type"="[a-z]+",
     *          "action"="find|clear"
     *     }
     * )
     * @Sidebar(name="Cache", icon="fas fa-database", parent="Admin")
     */
    public function cacheView(
        Request $request,
        string  $type   = '',
        string  $action = ''
    ): Response {
        $key = $request->get('key', null);

        LoggerContainer::getInstance()->trace(
            self::CONTROLLER_NAME . ".cache - type: '{$type}' action: '{$action}' key: '{$key}'",
            $this->context
        );

        $data     = '';
        $preClass = '';

        if ('' !== $type) {
            $cache = CacheHelper::get($type);

            switch ($action) {
                case 'find':
                    if (false === empty($key)) {
                        $data     = $cache->get($key, null);
                        $preClass = 'text-success';

                        if (null === $data) {
                            $data     = "No cache with key '{$key}' found!";
                            $preClass = 'text-danger';
                        }
                    } elseif ('' === $key) {
                        $data     = 'Please enter a key!';
                        $preClass = 'text-warning';
                    }
                    break;

                case 'clear':
                    if (false === empty($key)) {
                        $cache->delete($key);

                        $data = "Cache with key '{$key}' cleared!";
                    } else {
                        $cache->clear();

                        $data = 'Cache cleared!';
                    }

                    $preClass = 'text-success';
                    break;
            }
        }

        return $this->renderWithConfig(
            self::CONTROLLER_NAME . '/cache.html.twig',
            [
                'caches'   => CacheHelper::getAvailableCahces(),
                'active'   => $type,
                'key'      => $key,
                'data'     => $data,
                'preClass' => $preClass,
            ],
            self::CONTROLLER_NAME,
            'Cache',
            [self::CONTROLLER_NAME . '.cache']
        );
    }

    /**
     * @return Response
     *
     * @IsGranted("ROLE_RAISCH")
     * @Route("/admin/info", name="admin.info")
     * @Sidebar(name="Info", icon="fas fa-info-circle", parent="Admin")
     */
    public function infoView(): Response
    {
        LoggerContainer::getInstance()
            ->trace(self::CONTROLLER_NAME . '.info', $this->context);

        LoggerContainer::getInstance()
            ->error(new Exception('Test with Code', 500), $this->context);

        LoggerContainer::getInstance()
            ->info([
                'foo' => 'uno',
                'bar' => 'dos',
                'baz' => 'tres',
            ], $this->context);

        LoggerContainer::getInstance()
            ->error(new Exception('Test without Code'), $this->context);

        $roleHierarchy = $this->container->getParameter('security.role_hierarchy.roles');

        return $this->renderWithConfig(
            self::CONTROLLER_NAME . '/info.html.twig',
            [
                'infos' => [
                    'Role Hierarchy'        => print_r($roleHierarchy, true),
                    'Environment Variables' => print_r($_ENV, true),
                ],
            ],
            self::CONTROLLER_NAME,
            'Info',
            [self::CONTROLLER_NAME . '.info']
        );
    }
}
