<?php
namespace App\Controller\Abstracts;

use \Exception;
use App\Entity\User;
use App\Logger\LoggerContainer;
use App\Logger\LogLevel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * AbstractController class
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
abstract class AbstractController extends Controller
{
    public const SESSION_ROOT  = 'raisch';
    public const SESSION_THEME = self::SESSION_ROOT . '/theme';

    /**
     * Session Object
     *
     * @var Session
     */
    private $session = null;

    /**
     * AbstractController constructor
     *
     * @param KernelInterface $kernel
     *
     * @throws Exception
     */
    public function __construct(KernelInterface $kernel)
    {
        $this->addFileLogger(
            $kernel->getLogDir() . '/rd.app.log',
            explode(',', getenv('LOG_LEVEL'))
        );

        if ('dev' === $kernel->getEnvironment()) {
            $this->addFileLogger(
                $kernel->getLogDir() . '/rd.dev.log',
                explode(',', getenv('LOG_LEVEL_DEV')),
                true
            );
        }
    }

    /**
     * @param string $file
     * @param array  $logLevelNames
     * @param bool   $internal
     *
     * @throws Exception
     *
     * @return self
     */
    protected function addFileLogger(
        string $file,
        array  $logLevelNames,
        bool   $internal = false
    ): self {
        $levels          = [];
        $loggerContainer = LoggerContainer::getInstance();

        foreach ($logLevelNames as $name) {
            try {
                $levels[] = constant(LogLevel::class . '::' . strtoupper($name));
            } catch (Exception $ex) {
                // non existing log level => ignore
            }
        }

        if (true === $internal) {
            $fileLogger = $loggerContainer->addFileLogger($file);
            $fileLogger->addInstanceLogLevel(...$levels);
        } else {
            $loggerContainer
                ->addLogLevel(...$levels)
                ->addFileLogger($file);
        }

        return $this;
    }

    /**
     * @return array
     */
    protected function getBaseTemplateConfig(): array
    {
        /** @var \App\Entity\User $user */
        $user = $this->getUser();

        if ($user instanceof User && false === empty($user->getTheme())) {
            $theme = $user->getTheme();
        } else {
            $theme = $this->getSession()->get(self::SESSION_THEME, 'pink');
        }

        return [
            'pageTitle'        => 'Dashboard',
            'activeController' => [
                'name' => 'app',
                'sub'  => [],
            ],
            'brandText'        => 'Dashboard',
            'brandUrl'         => $this->generateAbsoluteUrl('app.index'),
            'theme'            => $theme,
        ];
    }

    /**
     * @param string $controller
     * @param string $title
     * @param array  $subPaths
     *
     * @return array
     */
    protected function buildConfig(
        string $controller,
        string $title    = '',
        array  $subPaths = []
    ): array {
        $title = ucfirst($controller) . ($title ? ' > ' . $title : '');

        if (false === empty($subPaths)) {
            $path = $subPaths[count($subPaths) - 1];
        } else {
            $path = $controller;
        }

        return [
            'pageTitle'        => $title,
            'activeController' => [
                'name' => $controller,
                'sub'  => $subPaths,
            ],
            'brandText'        => $title,
            'brandUrl'         => $this->generateAbsoluteUrl($path),
        ] + $this->getBaseTemplateConfig();
    }

    /**
     * @param string $template
     * @param array  $args
     * @param string $controller
     * @param string $title
     * @param array  $subPaths
     * @param array  $addConfig
     *
     * @return Response
     */
    public function renderWithConfig(
        string $template,
        array  $args,
        string $controller,
        string $title     = '',
        array  $subPaths  = [],
        array  $addConfig = []
    ): Response {
        return $this->render(
            $template,
            $args + ['config' => $addConfig + $this->buildConfig(
                $controller,
                $title,
                $subPaths
            )]
        );
    }

    /**
     * @return array
     */
    protected function getLastRoute(): array
    {
        $lastRoute = $this->getSession()->get(
            'last_route',
            [
                'name'   => 'app.index',
                'params' => [],
            ]
        );

        if (true === empty($lastRoute)) {
            $lastRoute = [
                'name'   => 'app.index',
                'params' => [],
            ];
        }

        if (true === empty($lastRoute['name'])) {
            $lastRoute['name'] = 'app.index';
        }

        if (false === is_array($lastRoute['params'])) {
            $lastRoute['params'] = [];
        }

        return $lastRoute;
    }

    /**
     * @return array
     */
    protected function getCurrentRoute(): array
    {
        $currentRoute = $this->getSession()->get(
            'this_route',
            [
                'name'   => 'app.index',
                'params' => [],
            ]
        );

        if (true === empty($currentRoute)) {
            $currentRoute = [
                'name'   => 'app.index',
                'params' => [],
            ];
        }

        if (true === empty($currentRoute['name'])) {
            $currentRoute['name'] = 'app.index';
        }

        if (false === is_array($currentRoute['params'])) {
            $currentRoute['params'] = [];
        }

        return $currentRoute;
    }

    /**
     * Provides data about the request, globals, environment etc.
     *
     * @param array $add additional data
     *
     * @return array combined data
     */
    protected function getDebug(array $add = []): array
    {
        $this->getSession(); // inits session if isnt

        return array_merge([
            'php.version'     => PHP_VERSION,
            'symfony.version' => Kernel::VERSION,
            'headers'         => getallheaders(),
            '$_GET'           => $_GET,
            '$_POST'          => $_POST,
            '$_COOKIE'        => $_COOKIE,
            '$_SESSION'       => $_SESSION,
            '$_FILES'         => $_FILES,
            '$_SERVER'        => $_SERVER,
            '$_ENV'           => $_ENV,
        ], $add);
    }

    /**
     * Generates absolute URL for given route name.
     *
     * @param string $routeName route name
     * @param array  $params    parameters for query string
     *
     * @return string absolute URL for given route name
     */
    protected function generateAbsoluteUrl(string $routeName, array $params = []): string
    {
        return $this->generateUrl(
            $routeName,
            $params,
            UrlGeneratorInterface::ABSOLUTE_URL
        );
    }

    /**
     * Gets the Session Object
     *
     * @return Session
     */
    protected function getSession(): Session
    {
        if (null === $this->session) {
            return $this->setDefaultSession()->session;
        }

        return $this->session;
    }

    /**
     * Sets the Session Object
     *
     * @param Session $session
     *
     * @return self
     */
    protected function setSession(Session $session): self
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Sets the default Session Object
     *
     * @return self
     */
    protected function setDefaultSession(): self
    {
        $this->session = new Session(
            new NativeSessionStorage(),
            new NamespacedAttributeBag()
        );

        if (null === $this->session->get(self::SESSION_ROOT, null)) {
            $this->session->set(self::SESSION_ROOT, []);
        }

        return $this;
    }
}
