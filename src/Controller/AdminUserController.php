<?php
namespace App\Controller;

use \Exception;
use \SplFileObject;
use App\Annotations\Sidebar;
use App\Controller\Abstracts\AbstractController;
use App\Entity\Alert;
use App\Entity\Role;
use App\Entity\User;
use App\Form\UserType;
use App\Logger\LoggerContainer;
use App\Repository\UserRepository;
use Doctrine\Common\Persistence\ObjectManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class AdminUserController
 *
 * @IsGranted("ROLE_ADMIN")
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
     * @param Request                      $request
     * @param UserPasswordEncoderInterface $encoder
     *
     * @return Response
     *
     * @Route("/admin/user/create", name="admin.user.create")
     */
    public function createView(
        Request $request,
        UserPasswordEncoderInterface $encoder
    ): Response {
        LoggerContainer::getInstance()->trace(
            AdminController::CONTROLLER_NAME . '.' . self::CONTROLLER_NAME . '.create',
            $this->context
        );

        $createFormOptions = [
            'container'       => $this->container,
            'repository_role' => $this->getDoctrine()
                ->getManager()
                ->getRepository(Role::class),
        ];

        $alerts = [];
        $user   = new User();
        $form   = $this->createForm(UserType::class, $user, $createFormOptions);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $password = $encoder->encodePassword($user, $user->getPlainPassword());

                $user->setPassword($password);
                $user->setCreatedBy($this->getUser());

                /** @var UploadedFile $avatar */
                $avatar = $user->getAvatar();

                /** @var SplFileObject $file */
                $file = $avatar->openFile();

                $user->setAvatar($file->fread($file->getSize()));
                $user->setAvatarMimeType($avatar->getMimeType());

                /** @var ObjectManager $manager */
                $manager = $this->getDoctrine()->getManager();
                $manager->persist($user);
                $manager->flush();

                $user->eraseCredentials();
                unset($user);

                $form = $this->createForm(UserType::class, new User(), $createFormOptions);

                $alerts[] = (new Alert())
                    ->setType('success')
                    ->setText('User sucessfully added.');
            } else {
                $alerts[] = (new Alert())
                    ->setType('warning')
                    ->setText('There was a problem while adding the User.');
            }
        }

        // $errors = [
        //     $form->getErrors(true),
        //     $form['email']->getErrors(true),
        //     $form['plainPassword']->getErrors(true),
        // ];

        // $alerts[] = (new Alert())
        //     ->setType('info')
        //     ->setText('Test Info Alert.');

        return $this->renderWithConfig(
            AdminController::CONTROLLER_NAME . '/' . self::CONTROLLER_NAME . '/create.html.twig',
            [
                'userAdminRoutes'   => $this->getInternalRoutes(),
                'userAdminCategory' => 'create a new users',
                'userAdminTitle'    => 'create',
                'userCreateAlerts'  => $alerts,
                // 'userCreateErrors'  => $errors,
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
     * @param int $id
     *
     * @return JsonResponse
     * @Route(
     *      "/admin/user/remove/{id}",
     *      name="admin.user.remove",
     *      requirements={"id"="[1-9][0-9]*"}
     * )
     */
    public function removeUser(int $id): JsonResponse
    {
        $response = ['status' => 200, 'text' => 'OK'];

        try {
            // TODO: set user to inactive/deleted and prevent login (keep dataset for relations)
            $user = $this->getDoctrine()
                ->getRepository(User::class)
                ->find($id);

            /** @var ObjectManager $manager */
            // $manager = $this->getDoctrine()->getManager();
            // $manager->remove($user);
            // $manager->flush();

            if (null === $user) {
                $response['status'] = 404;
                $response['text']   = 'User Nor Found';
            } else {
                $response['debug'] = $user->getEmail();
            }
        } catch (Exception $ex) {
            $response['status'] = 400;
            $response['text']   = 'Bad Request';
        }

        return JsonResponse::create($response);
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
