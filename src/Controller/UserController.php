<?php
namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

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
     *
     * @param KernelInterface $kernel
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
     *
     * @param  AuthenticationUtils $authUtils
     *
     * @return Response
     */
    public function loginView(AuthenticationUtils $authUtils): Response
    {
        $this->getLogger()->trace(self::CONTROLLER_NAME . '.login', $this->context);

        $error        = $authUtils->getLastAuthenticationError();
        $lastUsername = $authUtils->getLastUsername();

        return $this->render(self::CONTROLLER_NAME . '/login.html.twig', [
            'config'       => [
                'pageTitle'      => ucfirst(self::CONTROLLER_NAME) . ' Login',
                'contentClasses' => 'd-flex align-items-center',
                'showNavBar'     => false,
                'showSideBar'    => false,
                'showFooter'     => false,
            ] + $this->getBaseTemplateConfig(),
            'error'        => $error,
            'lastUsername' => $lastUsername,
        ]);
    }

    /**
     * @IsGranted("ROLE_USER")
     * @Route("/user/profile", name="user.profile")
     *
     * @return Response
     */
    public function profileView(): Response
    {
        $this->getLogger()->trace(self::CONTROLLER_NAME . '.profile', $this->context);

        return $this->render(self::CONTROLLER_NAME . '/profile.html.twig', [
            'config' => [
                'pageTitle'        => ucfirst(self::CONTROLLER_NAME) . ' Profile',
                'activeController' => [
                    'name' => self::CONTROLLER_NAME,
                    'sub'  => self::CONTROLLER_NAME . '.profile',
                ],
                'brandText'        => ucfirst(self::CONTROLLER_NAME) . ' Profile',
                'brandUrl'         => $this->generateAbsoluteUrl(
                    self::CONTROLLER_NAME . '.profile'
                ),
            ] + $this->getBaseTemplateConfig(),
        ]);
    }

    /**
     * @Route("/logout", name="user.logout")
     */
    public function logout()
    {
        $this->getLogger()->trace(self::CONTROLLER_NAME . '.logout', $this->context);
        // ...
    }
}
