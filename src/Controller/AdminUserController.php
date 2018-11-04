<?php
namespace App\Controller;

use \Exception;
use App\Annotations\Sidebar;
use App\Controller\Abstracts\AbstractController;
use App\Entity\User;
use App\Form\UserType;
use App\Logger\LoggerContainer;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class AdminUserController
 *
 * @IsGranted("ROLE_RAISCH")
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class AdminUserController extends AbstractController
{
    public const CONTROLLER_NAME    = 'user';
    public const SESSION_ADMIN_USER = self::SESSION_ROOT
        . '/' . AdminController::CONTROLLER_NAME
        . '/' . self::CONTROLLER_NAME;

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
        $this->context['__AREA__'] = 'AdminUserController';

        if (null === $this->getSession()->get(self::SESSION_ADMIN_USER, null)) {
            $this->getSession()->set(self::SESSION_ADMIN_USER, []);
        }
    }

    /**
     * @param UserRepository $userRepo
     *
     * @return Response
     *
     * @Route("/admin/user", name="admin.user.index")
     * @Sidebar(name="User Administration", icon="tim-icons icon-single-02", position=3, parent="Admin")
     */
    public function indexView(UserRepository $userRepo): Response
    {
        LoggerContainer::getInstance()->trace(
            AdminController::CONTROLLER_NAME . '.' . self::CONTROLLER_NAME . '.index',
            $this->context
        );

        /** @var User[] $users */
        $users = $userRepo->findAll();

        return $this->renderWithConfig(
            AdminController::CONTROLLER_NAME . '/' . self::CONTROLLER_NAME . '/index.html.twig',
            [
                'userAdminRoutes'   => $this->getInternalRoutes(),
                'userAdminCategory' => 'list of all users',
                'userAdminTitle'    => 'overview',
                'users'             => $users,
            ],
            AdminController::CONTROLLER_NAME,
            'User Administration',
            [AdminController::CONTROLLER_NAME . '.' . self::CONTROLLER_NAME . '.index']
        );
    }

    /**
     * @return Response
     *
     * @Route("/admin/user/create", name="admin.user.create")
     */
    public function createView(Request $request, UserPasswordEncoderInterface $encoder): Response
    {
        LoggerContainer::getInstance()->trace(
            AdminController::CONTROLLER_NAME . '.' . self::CONTROLLER_NAME . '.create',
            $this->context
        );

        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $password = $encoder->encodePassword($user, $user->getPlainPassword());

            $user->setPassword($password);

            /** @var ObjectManager $manager */
            $manager = $this->getDoctrine()->getManager();
            $manager->persist($user);
            $manager->flush();

            $form = $this->createForm(UserType::class, new User());
        }

        $errors = [
            $form->getErrors(true),
            $form['email']->getErrors(true),
            $form['plainPassword']->getErrors(true),
        ];

        return $this->renderWithConfig(
            AdminController::CONTROLLER_NAME . '/' . self::CONTROLLER_NAME . '/create.html.twig',
            [
                'userAdminRoutes'   => $this->getInternalRoutes(),
                'userAdminCategory' => 'create a new users',
                'userAdminTitle'    => 'create',
                'userCreateErrors'  => $errors,
                'userCreateForm'    => $form->createView(),
            ],
            AdminController::CONTROLLER_NAME,
            'User Administration',
            [
                AdminController::CONTROLLER_NAME . '.' . self::CONTROLLER_NAME . '.index',
                AdminController::CONTROLLER_NAME . '.' . self::CONTROLLER_NAME . '.create',
            ]
        );
    }

    /**
     * @return string[][]
     */
    private function getInternalRoutes(): array
    {
        return [
            'admin.user.index' => [
                'text' => 'overview',
                'url'  => $this->generateAbsoluteUrl('admin.user.index'),
            ],
            'admin.user.create' => [
                'text' => 'create',
                'url'  => $this->generateAbsoluteUrl('admin.user.create'),
            ],
            // TODO: add search field
            'todo' => [
                'text' => 'TODO: add search field',
                'url'  => '#',
            ],
            // '{ROUTE_NAME}' => [
            //     'text' => '{LINK_TEXT}',
            //     'url'  => $this->generateAbsoluteUrl({ROUTE_NAME}),
            // ],
        ];
    }
}
