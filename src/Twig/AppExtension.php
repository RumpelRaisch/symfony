<?php
namespace App\Twig;

use \DateTime;
use App\Entity\Alert;
use App\Services\Helper\SidebarHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * AppExtension class
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
class AppExtension extends AbstractExtension
{
    /** @var ContainerInterface */
    private $container;

    /**
     * AppExtension constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /** {@inheritdoc} */
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'Sidebar',
                [$this, 'renderSidebar'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'Alert',
                [$this, 'renderAlert'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /** {@inheritdoc} */
    public function getFilters(): array
    {
        return [
            new TwigFilter('cacheHack', [$this, 'cacheHack']),
            new TwigFilter('printr', [$this, 'printR']),
            new TwigFilter('formatDateTimeGitHub', [$this, 'formatDateTimeGitHub']),
        ];
    }

    /**
     * @param array $activeController
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * @return string
     */
    public function renderSidebar(array $activeController): string
    {
        /** @var SidebarHelper $sidebarHelper */
        $sidebarHelper = $this->container->get(
            'raisch.sidebar.helper'
        );

        return $this->container->get('twig')->render(
            'twig_extensions/sidebar.html.twig',
            [
                'items'            => $sidebarHelper->get(),
                'activeController' => $activeController,
            ]
        );
    }

    /**
     * @param Alert $alert
     *
     * @throws \Twig_Error_Loader
     * @throws \Twig_Error_Runtime
     * @throws \Twig_Error_Syntax
     *
     * @return string
     */
    public function renderAlert(Alert $alert): string
    {
        return $this->container->get('twig')->render(
            'twig_extensions/alert.html.twig',
            ['alert' => $alert]
        );
    }

    /**
     * [formatDateTimeGitHub description]
     *
     * @param string $date   [description]
     * @param string $format [description]
     *
     * @return string [description]
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
     * @param [type] $data [description]
     *
     * @return string [description]
     */
    public function printR($data): string
    {
        return print_r($data, true);
    }

    /**
     * Cachehack filter for assets not maintained by Webpack Encore.
     *
     * usage in twig templates:
     *      {{ asset('path/without/leading/slash/file_name.ext')|cacheHack }}
     *      {{ absolute_url(asset('path/without/leading/slash/file_name.ext')|cacheHack) }}
     *
     * @param string $file filepath given by twigs asset() function
     *
     * @return string file path with query based on last edit of file
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
