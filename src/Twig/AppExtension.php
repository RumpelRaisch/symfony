<?php
namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

use \DateTime;

/**
 * [AppExtension description]
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class AppExtension extends AbstractExtension
{
    /**
     * [getFilters description]
     *
     * @return array [description]
     */
    public function getFilters(): array
    {
        return [
            new TwigFilter('cacheHack', [$this, 'cacheHack']),
            new TwigFilter('printr', [$this, 'printR']),
            new TwigFilter('formatDateTimeGitHub', [$this, 'formatDateTimeGitHub']),
        ];
    }

    /**
     * [formatDateTimeGitHub description]
     *
     * @param  string $date   [description]
     * @param  string $format [description]
     * @return string         [description]
     */
    public function formatDateTimeGitHub(
        string $date,
        string $format = 'Y-m-d H:i:s'
    ): string {
        return DateTime::createFromFormat(DateTime::ISO8601, $date)
            ->format($format);
    }

    /**
     * [printR description]
     *
     * @param  [type] $data [description]
     * @return string       [description]
     */
    public function printR($data): string
    {
        return print_r($data);
    }

    /**
     * Cachehack filter for assets not maintained by Webpack Encore.
     *
     * usage in twig templates:
     *      {{ asset('path/without/leading/slash/file_name.ext')|cacheHackS }}
     *      {{ absolute_url(asset('path/without/leading/slash/file_name.ext')|cacheHack) }}
     *
     * @param  string $file filepath given by twigs asset() function
     * @return string       file path with query based on last edit of file
     */
    public function cacheHack(string $file): string
    {
        $file_path = __DIR__ . '/../../public/' . $file;

        if (true === is_file($file_path)) {
            return $file . '?t=' . filemtime(__DIR__ . '/../../public/' . $file);
        }

        return $file;
    }
}
