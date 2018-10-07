<?php
namespace App\Helper;

use App\Logger\SimpleFileLogger;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerInterface;

use \DateTime;

/**
 * [GitHubApiHelper description]
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class GitHubApiHelper implements LoggerAwareInterface
{
    /**
     * [private description]
     *
     * @var string
     */
    private $dirCache = null;

    /**
     * [private description]
     *
     * @var string
     */
    private $dirTemp  = null;

    /**
     * [private description]
     *
     * @var integer
     */
    private $cacheLifeTime = null;

    /**
     * [private description]
     *
     * @var SimpleFileLogger
     */
    private $logger = null;

    /**
     * Constructor.
     */
    public function __construct(
        SimpleFileLogger $logger,
        string           $dirCache,
        string           $dirTemp,
        int              $cacheLifeTime = 3600
    ) {
        $this
            ->setLogger($logger)
            ->setDirCache($dirCache)
            ->setDirTemp($dirTemp)
            ->setcacheLifeTime($cacheLifeTime);
    }

    /**
     * [getGitHubRepoOverview description]
     *
     * @param string $user [description]
     *
     * @return array        [description]
     */
    public function getGitHubRepoOverview(string $user): array
    {
        $file  = "{$this->getDirCache()}/github/{$user}.repos.json";
        $cache = $this->getGithubCache($file);
        $repos = null;
        $all   = [];

        if (true === empty($cache)) {
            $apiResponse = $this->callGitHubAPI("users/{$user}/repos", true);

            if (
                200  !== $apiResponse['headers']['status']['code'] ||
                true === empty($apiResponse['data'])
            ) {
                $repos = $this->getGithubCache($file, true);

                if (true === empty($repos)) {
                    return ['all' => [], 'data' => []];
                }
            } else {
                $repos = $apiResponse['data'];
            }

            $this->setGithubCache($file, $repos);
        } else {
            $repos = $cache;
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

            if (
                false === empty($repo['participation']['all']) &&
                52    === count($repo['participation']['all'])
            ) {
                foreach ($repo['participation']['all'] as $i => $n) {
                    $all[$i] = true === isset($all[$i]) ? $all[$i] + $n : $n;
                }
            }
        }

        return ['all' => $all, 'data' => $repos];
    }

    /**
     * [getGithubRepoParticipation description]
     *
     * @param int    $id       [description]
     * @param string $fullName [description]
     *
     * @return array            [description]
     */
    public function getGithubRepoParticipation(int $id, string $fullName): array
    {
        $file  = $this->getDirCache() . "/github/participation/repo.{$id}.json";
        $cache = $this->getGithubCache($file);

        if (
            true === empty($cache) ||
            true === empty($cache['all'])
        ) {
            $apiResponse = $this->callGitHubAPI("repos/{$fullName}/stats/participation");

            if (
                200  !== $apiResponse['headers']['status']['code'] ||
                true === empty($apiResponse['data']) ||
                true === empty($apiResponse['data']['all'])
            ) {
                $participation = $this->getGithubCache($file, true);

                if (
                    true === empty($participation) ||
                    true === empty($participation['all'])
                ) {
                    return ['all' => [], 'owner' => []];
                }
            } else {
                $participation = $apiResponse['data'];
            }

            $this->setGithubCache($file, $participation);
        } else {
            $participation = $cache;
        }

        return $participation;
    }

    /**
     * [getGithubCache description]
     *
     * @param string     $file [description]
     *
     * @return null|array       [description]
     */
    private function getGithubCache(
        string $file,
        bool   $ignoreLifetime = false
    ): ?array {
        $dir = dirname($file);

        if (false === is_dir($dir)) {
            mkdir($dir, 0664, true);
        }

        if (true === is_file($file)) {
            if (
                true === $ignoreLifetime ||
                $this->getCacheLifeTime() > (time() - filemtime($file))
            ) {
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
     * @param string  $path        [description]
     * @param boolean $saveHeaders [description]
     *
     * @return array                [description]
     */
    private function callGitHubAPI(string $path, bool $saveHeaders = null): array
    {
        $curl    = curl_init();
        $headers = [];

        curl_setopt($curl, CURLOPT_URL, 'https://api.github.com/' . ltrim($path, '/'));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($curl, CURLOPT_USERAGENT, 'https://bitshifting.de');
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            'Accept: application/vnd.github.v3+json',
            'Time-Zone: Europe/Berlin',
            'Authorization: token ' . getenv('GITHUB_PERSONAL_ACCESS_TOKEN'),
        ]);
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
                    $headers[$name] = trim($header[1]);
                } else {
                    if (false === is_array($headers[$name])) {
                        $headers[$name] = [$headers[$name]];
                    }

                    $headers[$name][] = trim($header[1]);
                }

                return $len;
            }
        );

        $response = curl_exec($curl);

        curl_close($curl);

        if (true === empty($headers['status'])) {
            $headers['status'] = '0 No Status Header Found';
        } elseif (true === is_array($headers['status'])) {
            $headers['status'] = '500 More Than One Status Header Found';
        }

        $matches = [];

        preg_match(
            '/^(?P<code>[0-9]+)? ?(?P<message>.*)?$/',
            $headers['status'],
            $matches
        );

        $headers['status'] = [
            'code'    => ('' === $matches['code'])
                ? 0
                : (int) $matches['code'],
            'message' => ('' === $matches['message'])
                ? 'No Status Message'
                : $matches['message'],
        ];

        if (true === $saveHeaders) {
            $file = $this->getDirTemp() . '/github.headers.txt';
            $dir  = dirname($file);

            if (false === is_dir($dir)) {
                mkdir($dir, 0664, true);
            }

            file_put_contents(
                $file,
                print_r($headers, true)
            );
        }

        $this->getLogger()->debugR($headers, [
            '__AREA__' => 'GitHubApiHelper->callGitHubAPI()',
        ]);

        if (false === $response) {
            return ['headers' => $headers, 'data' => []];
        }

        $response = json_decode($response, true);
        $response = $response ? $response : [];

        return ['headers' => $headers, 'data' => $response];
    }

    /**
     * Get the value of [private description]
     *
     * @return string
     */
    public function getDirCache()
    {
        return $this->dirCache;
    }

    /**
     * Set the value of [private description]
     *
     * @param string $dirCache
     *
     * @return GitHubApiHelper
     */
    public function setDirCache($dirCache): GitHubApiHelper
    {
        $this->dirCache = $dirCache;

        return $this;
    }

    /**
     * Get the value of [private description]
     *
     * @return string
     */
    public function getDirTemp()
    {
        return $this->dirTemp;
    }

    /**
     * Set the value of [private description]
     *
     * @param string $dirTemp
     *
     * @return GitHubApiHelper
     */
    public function setDirTemp($dirTemp): GitHubApiHelper
    {
        $this->dirTemp = $dirTemp;

        return $this;
    }

    /**
     * Get the value of [private description]
     *
     * @return integer
     */
    public function getCacheLifeTime()
    {
        return $this->cacheLifeTime;
    }

    /**
     * Set the value of [private description]
     *
     * @param integer $cacheLifeTime
     *
     * @return GitHubApiHelper
     */
    public function setCacheLifeTime($cacheLifeTime): GitHubApiHelper
    {
        $this->cacheLifeTime = $cacheLifeTime;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setLogger(LoggerInterface $logger): GitHubApiHelper
    {
        $this->logger = $logger;

        return $this;
    }

    /**
     * Get the value of [private description]
     *
     * @return SimpleFileLogger
     */
    private function getLogger()
    {
        return $this->logger;
    }
}
