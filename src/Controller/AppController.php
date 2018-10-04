<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Helper\LoremIpsumHelper;

/**
 * [AppController description].
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class AppController extends Abstracts\AbstractController
{
    public const SESSION_APP = self::SESSION_ROOT . '/app';

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
    public function index(Request $request): Response
    {
        return $this->render('app/index.html.twig', [
            'title'         => 'Dashboard',
            'controller'    => 'app',
            'brandText'     => 'Dashboard',
            'brandUrl'      => $this->generateAbsoluteUrl('app.index'),
            'lorem'         => new LoremIpsumHelper(),
            'repos'         => $this->getGitHubRepoOverview(),
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

    private function getGitHubRepoOverview(): array
    {
        // TODO: only one request every hour, save data for one hour

        $repos = $this->callGitHubAPI('users/RumpelRaisch/repos');

        foreach ($repos as &$repo) {
            $repo['participation'] = $this->callGitHubAPI("repos/{$repo['full_name']}/stats/participation");
        }

        return $repos;
    }

    /**
     * [callGitHubAPI description]
     *
     * @param  string $path [description]
     * @return array        [description]
     */
    private function callGitHubAPI(string $path): array
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, 'https://api.github.com/' . ltrim($path, '/'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 5);
        curl_setopt($curl, CURLOPT_USERAGENT, 'https://bitshifting.de');
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Accept: application/vnd.github.v3+json',
            'Time-Zone: Europe/Berlin',
        ]);

        $response = curl_exec($curl);

        curl_close($curl);

        if (false === $response) {
            return [];
        }

        return json_decode($response, true);
    }
}
