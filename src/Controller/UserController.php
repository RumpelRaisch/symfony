<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * [UserController description]
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class UserController extends Abstracts\AbstractController
{
    public const CONTROLLER_NAME = 'user';
    public const SESSION_USER    = self::SESSION_ROOT . '/' . self::CONTROLLER_NAME;

    private $context = [];

    /**
     * Constructor.
     */
    public function __construct(KernelInterface $kernel)
    {
        parent::__construct();

        $this
            ->setDefaultSession()
            ->setDefaultLogger($kernel);

        $this->context['__AREA__'] = 'UserController';

        if (null === $this->getSession()->get(self::SESSION_USER, null)) {
            $this->getSession()->set(self::SESSION_USER, []);
        }
    }

    /**
     * @Route("/login", name="user.login")
     */
    public function loginView(): Response
    {
        $this->getLogger()->trace(self::CONTROLLER_NAME . '.login', $this->context);

        return $this->render(self::CONTROLLER_NAME . '/login.html.twig', [
            'config' => [
                'pageTitle'      => ucfirst(self::CONTROLLER_NAME) . ' Login',
                'contentClasses' => 'd-flex align-items-center',
                'showNavBar'     => false,
                'showSideBar'    => false,
                'showFooter'     => false,
            ] + $this->getBaseTemplateConfig(),
        ]);
    }
}
