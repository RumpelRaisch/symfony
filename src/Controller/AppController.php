<?php
namespace App\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Helper\LoremIpsumHelper;

use \DateTime;

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
        return $this->render('app/index.html.twig', [
            'config'    => $this->getBaseTemplateConfig($this->getSession()),
            'lorem'     => new LoremIpsumHelper(),
            'repos'     => $this->getGitHubRepoOverview(),
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

    /**
     * [getGitHubRepoOverview description]
     *
     * @return array [description]
     */
    private function getGitHubRepoOverview(): array
    {
        $file  = self::DIR_CACHE . 'github/repos.json';
        $repos = $this->getGithubCache($file);

        if (true === empty($repos)) {
            $repos = $this->callGitHubAPI('users/RumpelRaisch/repos', true);

            if (true === empty($repos)) {
                return [];
            }

            $this->setGithubCache($file, $repos);
        }

        foreach ($repos as &$repo) {
            $repo['created_at'] = DateTime::createFromFormat(
                DateTime::ISO8601,
                $repo['created_at']
            )->format('Y-m-d H:i:s');
            $repo['updated_at'] = DateTime::createFromFormat(
                DateTime::ISO8601,
                $repo['updated_at']
            )->format('Y-m-d H:i:s');
            $repo['participation'] = $this->getGithubRepoParticipation($repo['id'], $repo['full_name']);
        }

        return $repos;
    }

    /**
     * [getGithubRepoParticipation description]
     *
     * @param  int    $id   [description]
     * @param  string $name [description]
     * @return array        [description]
     */
    private function getGithubRepoParticipation(int $id, string $name): array
    {
        $file          = self::DIR_CACHE . "github/participation/repo.{$id}.json";
        $participation = $this->getGithubCache($file);

        if (
            true === empty($participation) ||
            true === empty($participation['all'])
        ) {
            $participation = $this->callGitHubAPI("repos/{$name}/stats/participation");

            if (
                true === empty($participation) ||
                true === empty($participation['all'])
            ) {
                return ['all' => []];
            }

            $this->setGithubCache($file, $participation);
        }

        return $participation;
    }

    /**
     * [getGithubCache description]
     *
     * @param  string     $file [description]
     * @return null|array       [description]
     */
    private function getGithubCache(string $file): ?array
    {
        $dir = dirname($file);

        if (false === is_dir($dir)) {
            mkdir($dir, 0664, true);
        }

        if (true === is_file($file)) {
            if (3600 > time() - filemtime($file)) {
                $json = file_get_contents($file);
                $data = json_decode($json, true);

                if (false === empty($data)) {
                    return $data;
                } else {
                    unlink($file);
                }
            } else {
                unlink($file);
            }
        }

        return null;
    }

    /**
     * [setGithubCache description]
     *
     * @param string $file [description]
     * @param array  $data [description]
     */
    private function setGithubCache(string $file, array $data)
    {
        file_put_contents($file, json_encode($data, JSON_HEX_QUOT));
    }

    /**
     * [callGitHubAPI description]
     *
     * @param  string $path [description]
     * @return array        [description]
     */
    private function callGitHubAPI(string $path, bool $saveHeaders = null): array
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, 'https://api.github.com/' . ltrim($path, '/'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_USERAGENT, 'https://bitshifting.de');
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Accept: application/vnd.github.v3+json',
            'Time-Zone: Europe/Berlin',
            'Authorization: token ' . getenv('GITHUB_PERSONAL_ACCESS_TOKEN'),
        ]);

        if (true === $saveHeaders) {
            $headers = [];

            curl_setopt(
                $curl,
                CURLOPT_HEADERFUNCTION,
                function ($curl, $header) use (&$headers) {
                    $len    = strlen($header);
                    $header = explode(':', $header, 2);

                    if (2 > count($header)) {
                        return $len;
                    }

                    $name = strtolower(trim($header[0]));

                    if (false === array_key_exists($name, $headers)) {
                        $headers[$name] = [trim($header[1])];
                    } else {
                        $headers[$name][] = trim($header[1]);
                    }

                    return $len;
                }
            );
        }

        $response = curl_exec($curl);

        curl_close($curl);

        if (true === $saveHeaders) {
            file_put_contents(
                self::DIR_TEMP . 'github.headers.txt',
                print_r($headers, true)
            );
        }

        if (false === $response) {
            return [];
        }

        $response = json_decode($response, true);

        return $response ? $response : [];
    }
}
