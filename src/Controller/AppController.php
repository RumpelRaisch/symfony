<?php
namespace App\Controller;

use \Exception;
use App\Annotations\Sidebar;
use App\Helper\GitHubApiHelper;
use App\Helper\LoremIpsumHelper;
use App\Logger\LoggerContainer;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * AppController class
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class AppController extends Abstracts\AbstractController
{
    public const CONTROLLER_NAME = 'app';
    public const SESSION_APP     = self::SESSION_ROOT . '/' . self::CONTROLLER_NAME;

    private $context = [];

    /**
     * Constructor.
     *
     * @param KernelInterface $kernel
     *
     * @throws Exception
     */
    public function __construct(KernelInterface $kernel)
    {
        parent::__construct($kernel);

        $this->setDefaultSession();
        $this->context['__AREA__'] = 'AppController';

        if (null === $this->getSession()->get(self::SESSION_APP, null)) {
            $this->getSession()->set(self::SESSION_APP, []);
        }
    }

    /**
     * @return Response
     *
     * @Route("/", name="app.index")
     * @Sidebar(name="Dashboard", icon="tim-icons icon-chart-pie-36", position=1)
     */
    public function indexView(): Response
    {
        LoggerContainer::getInstance()
            ->trace(self::CONTROLLER_NAME . '.index', $this->context);

        $gitHubApiHelper = new GitHubApiHelper(
            $this->get('kernel')->getAppCacheDir(),
            $this->get('kernel')->getAppTempDir()
        );

        return $this->render(self::CONTROLLER_NAME . '/index.html.twig', [
            'config'    => $this->getBaseTemplateConfig(),
            'lorem'     => new LoremIpsumHelper(),
            'repos'     => $gitHubApiHelper->getGitHubRepoOverview('RumpelRaisch'),
            'dumpRepos' => false,
        ]);
    }
}
