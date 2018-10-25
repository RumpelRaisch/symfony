<?php
namespace App\Controller;

use App\Helper\GitHubApiHelper;
use App\Helper\LoremIpsumHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * [AppController description].
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class AppController extends Abstracts\AbstractController
{
    public const CONTROLLER_NAME = 'app';
    public const SESSION_APP     = self::SESSION_ROOT . '/' . self::CONTROLLER_NAME;

    private $context = [];

    /**
     * Response Objekt.
     *
     * @var Response
     */
    private $response = null;

    /**
     * Constructor.
     */
    public function __construct(KernelInterface $kernel)
    {
        parent::__construct();

        $this
            ->setDefaultSession()
            ->setDefaultLogger($kernel);

        $this->context['__AREA__'] = 'AppController';

        if (null === $this->getSession()->get(self::SESSION_APP, null)) {
            $this->getSession()->set(self::SESSION_APP, []);
        }
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Route("/", name="app.index")
     */
    public function indexView(Request $request): Response
    {
        $this->getLogger()->trace(self::CONTROLLER_NAME . '.index', $this->context);

        $gitHubApiHelper = new GitHubApiHelper(
            $this->getLogger(),
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

    /**
     * [prepareDefaultResponse description]
     *
     * @return Response [description]
     */
    private function prepareDefaultResponse(): Response
    {
        $this->response = null;
        $this->response = new Response();
        $this->response
            ->setStatusCode(Response::HTTP_OK)
            ->setCharset('UTF-8')
            ->headers->set('Content-Type', 'text/plain');

        return $this->response;
    }
}
