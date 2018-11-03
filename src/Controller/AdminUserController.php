<?php
namespace App\Controller;

use \Exception;
use App\Annotations\Sidebar;
use App\Controller\Abstracts\AbstractController;
use App\Logger\LoggerContainer;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

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
     * @Route("/admin/user", name="admin.user.index")
     * @Sidebar(name="User Administration", icon="tim-icons icon-single-02", position=3, parent="Admin")
     */
    public function indexView()
    {
        LoggerContainer::getInstance()->trace(
            AdminController::CONTROLLER_NAME . '.' . self::CONTROLLER_NAME . '.index',
            $this->context
        );

        return $this->renderWithConfig(
            AdminController::CONTROLLER_NAME . '/' . self::CONTROLLER_NAME . '/index.html.twig',
            [
                'userAdminRoutes'   => $this->getRoutes(),
                'userAdminCategory' => 'list of all users',
                'userAdminTitle'    => 'overview',
            ],
            AdminController::CONTROLLER_NAME,
            'User Administration',
            [AdminController::CONTROLLER_NAME . '.' . self::CONTROLLER_NAME . '.index']
        );
    }

    /**
     * @Route("/admin/user/create", name="admin.user.create")
     */
    public function createView()
    {
        LoggerContainer::getInstance()->trace(
            AdminController::CONTROLLER_NAME . '.' . self::CONTROLLER_NAME . '.create',
            $this->context
        );

        return $this->renderWithConfig(
            AdminController::CONTROLLER_NAME . '/' . self::CONTROLLER_NAME . '/create.html.twig',
            [
                'userAdminRoutes'   => $this->getRoutes(),
                'userAdminCategory' => 'create a new users',
                'userAdminTitle'    => 'create',
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
    private function getRoutes(): array
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
