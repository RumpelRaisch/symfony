<?php
namespace App\Controller;

use \Exception;
use App\Facades\UserFacade;
use App\Form\UserAssertType;
use App\Helper\LoremIpsumHelper;
use App\Logger\LoggerContainer;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

/**
 * Class UserController
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class UserController extends Abstracts\AbstractController
{
    public const CONTROLLER_NAME = 'user';
    public const SESSION_USER    = self::SESSION_ROOT . '/' . self::CONTROLLER_NAME;

    private $context = [];

    /**
     * UserController constructor.
     *
     * @param KernelInterface $kernel
     *
     * @throws Exception
     */
    public function __construct(KernelInterface $kernel)
    {
        parent::__construct($kernel);

        $this->setDefaultSession();
        $this->context['__AREA__'] = 'UserController';

        if (null === $this->getSession()->get(self::SESSION_USER, null)) {
            $this->getSession()->set(self::SESSION_USER, []);
        }
    }

    /**
     * @param AuthenticationUtils $authUtils
     *
     * @return Response
     *
     * @Route("/login", name="user.login")
     */
    public function loginView(AuthenticationUtils $authUtils): Response
    {
        LoggerContainer::getInstance()
            ->trace(self::CONTROLLER_NAME . '.login', $this->context);

        $error        = $authUtils->getLastAuthenticationError();
        $lastUsername = $authUtils->getLastUsername();

        return $this->renderWithConfig(
            self::CONTROLLER_NAME . '/login.html.twig',
            [
                'error'        => $error,
                'lastUsername' => $lastUsername,
            ],
            self::CONTROLLER_NAME,
            'Login',
            [self::CONTROLLER_NAME . '.login'],
            [
                'contentClasses' => 'd-flex align-items-center',
                'showNavBar'     => false,
                'showSideBar'    => false,
                'showFooter'     => false,
            ]
        );
    }

    /**
     * @param Request       $request
     * @param ObjectManager $manager
     *
     * @return Response
     *
     * @IsGranted("ROLE_USER")
     * @Route("/user/profile", name="user.profile")
     */
    public function profileView(
        Request       $request,
        ObjectManager $manager
    ): Response {
        LoggerContainer::getInstance()
            ->trace(self::CONTROLLER_NAME . '.profile', $this->context);

        /** @var \App\Entity\User $user */
        $user       = $this->getUser();
        $userFacade = UserFacade::createFromUser($user, $manager);

        $form = $this->createForm(
            UserAssertType::class,
            $userFacade->getUserAssert()
        );

        $form->handleRequest($request);

        if (true === $form->isSubmitted()) {
            LoggerContainer::getInstance()->trace(
                self::CONTROLLER_NAME . '.profile - form submitted',
                $this->context
            );

            if (true === $form->isValid()) {
                LoggerContainer::getInstance()->trace(
                    self::CONTROLLER_NAME . '.profile - form is valid',
                    $this->context
                );

                $userFacade
                    ->syncUserAssertToUser()
                    ->saveUser();
            } else {
                LoggerContainer::getInstance()->trace(
                    self::CONTROLLER_NAME . '.profile - form is NOT valid',
                    $this->context
                );
            }
        }

        return $this->renderWithConfig(
            self::CONTROLLER_NAME . '/profile.html.twig',
            [
                'form'  => $form->createView(),
                'lorem' => new LoremIpsumHelper(),
            ],
            self::CONTROLLER_NAME,
            'Profile',
            [self::CONTROLLER_NAME . '.profile']
        );
    }

    /**
     * @Route("/logout", name="user.logout")
     */
    public function logout(): void
    {
        LoggerContainer::getInstance()
            ->trace(self::CONTROLLER_NAME . '.logout', $this->context);
    }
}
