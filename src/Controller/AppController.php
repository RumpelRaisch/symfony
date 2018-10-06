<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Helper\GitHubApiHelper;
use App\Helper\LoremIpsumHelper;

/**
 * [AppController description].
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class AppController extends Abstracts\AbstractController
{
    public const CONTROLLER_NAME = 'app';
    public const SESSION_APP     = self::SESSION_ROOT . '/app';

    /**
     * Response Objekt.
     *
     * @var Response
     */
    private $response = null;

    /**
     * Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        $this->setDefaultSession();

        if (null === $this->getSession()->get(self::SESSION_APP, null)) {
            $this->getSession()->set(self::SESSION_APP, []);
        }
    }

    /**
     * @Route("/", name="app.index")
     */
    public function indexView(Request $request): Response
    {
        $gitHubApiHelper = new GitHubApiHelper(self::DIR_CACHE, self::DIR_TEMP);

        return $this->render('app/index.html.twig', [
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
