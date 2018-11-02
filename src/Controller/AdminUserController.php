<?php
namespace App\Controller;

use \Exception;
use App\Annotations\Sidebar;
use App\Controller\Abstracts\AbstractController;
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
    public function index()
    {
        return $this->renderWithConfig(
            AdminController::CONTROLLER_NAME . '/' . self::CONTROLLER_NAME . '/index.html.twig',
            [],
            AdminController::CONTROLLER_NAME,
            'User Administration',
            'user.index'
        );
    }
}
