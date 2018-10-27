<?php
namespace App\Controller\Abstracts;

use \Exception;
use App\Entity\User;
use App\Logger\LoggerContainer;
use App\Logger\LogLevel;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * [AbstractController description]
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
     * Constructor.
     *
     * @param KernelInterface $kernel
     *
     * @throws Exception
     */
    public function __construct(KernelInterface $kernel)
    {
        $envLogLevels = explode(',', getenv('LOG_LEVEL'));
        $levels       = [];

        foreach ($envLogLevels as $logLevel) {
            try {
                $levels[] = constant(LogLevel::class . '::' . $logLevel);
            } catch (Exception $ex) {
                // non existing log level => ignore
            }
        }

        LoggerContainer::getInstance()
            ->addLogLevel(...$levels)
            ->addFileLogger($kernel->getLogDir() . '/app.log');
    }

    /**
     * [getBaseTemplateConfig description]
     *
     * @return array [description]
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
                'sub'  => '',
            ],
            'brandText'        => 'Dashboard',
            'brandUrl'         => $this->generateAbsoluteUrl('app.index'),
            'theme'            => $theme,
        ];
    }

    /**
     * [getLastRoute description]
     *
     * @return array [description]
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
     * [getCurrentRoute description]
     *
     * @return array [description]
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
     * @author Rainer Schulz <rainer.schulz@bitshifting.de>
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
     * @return AbstractController
     */
    protected function setSession(Session $session): AbstractController
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Sets the default Session Object
     *
     * @return AbstractController
     */
    protected function setDefaultSession(): AbstractController
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
