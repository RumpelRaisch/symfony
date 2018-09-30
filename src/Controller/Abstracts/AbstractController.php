<?php
namespace App\Controller\Abstracts;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Session\Attribute\NamespacedAttributeBag;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use Symfony\Component\HttpKernel\Kernel;

abstract class AbstractController extends Controller
{
    /**
     * Session Objekt
     *
     * @var Session
     */
    private $session = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        // parent::__construct();
    }

    /**
     * Provides data about the request, globals, environment etc.
     *
     * @param  array $add additional data
     * @return array      combined data
     */
    protected function getDebug(array $add = []): array
    {
        return array_merge([
            'php.version'     => PHP_VERSION,
            'symfony.version' => Kernel::VERSION,
            'headers'         => getallheaders(),
            '$_GET'           => $_GET,
            '$_POST'          => $_POST,
            '$_COOKIE'        => $_COOKIE,
            '$_FILES'         => $_FILES,
            '$_SERVER'        => $_SERVER,
            '$_ENV'           => $_ENV,
        ], $add);
    }

    /**
     * Gets the Session Objekt
     *
     * @return Session
     */
    protected function getSession()
    {
        return $this->session;
    }

    /**
     * Sets the Session Objekt
     *
     * @param Session $session
     *
     * @return self
     */
    protected function setSession(Session $session): AbstractController
    {
        $this->session = $session;

        return $this;
    }

    /**
     * Sets the default Session Objekt
     *
     * @return self
     */
    protected function setDefaultSession(): ?AbstractController
    {
        $this->session = new Session(
            new NativeSessionStorage(),
            new NamespacedAttributeBag()
        );

        return $this;
    }
}
