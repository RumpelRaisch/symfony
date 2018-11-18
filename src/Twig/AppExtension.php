<?php
namespace App\Twig;

use App\Entity\Alert;
use App\Entity\User;
use App\Services\Helper\SidebarHelper;
use App\Services\User\Hierarchy;
use DateTime;
use Psr\SimpleCache\InvalidArgumentException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use Twig_Error_Loader;
use Twig_Error_Runtime;
use Twig_Error_Syntax;

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
        // loading other services inside this service is too early at this point
        // /** @var Hierarchy $hierarchy */
        // $hierarchy = $this->container->get('raisch.user.hierarchy');

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
            new TwigFunction(
                'CanAlterUser',
                // [$hierarchy, 'canAlterUser']
                [$this, 'canAlterUser']
            ),
            new TwigFunction(
                'CanDeactivateUser',
                // [$hierarchy, 'canDeactivateUser']
                [$this, 'canDeactivateUser']
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
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
     * @throws InvalidArgumentException
     *
     * @return string
     */
    public function renderSidebar(array $activeController): string
    {
        /** @var SidebarHelper $sidebarHelper */
        $sidebarHelper = $this->container->get('raisch.sidebar.helper');

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
     * @throws Twig_Error_Loader
     * @throws Twig_Error_Runtime
     * @throws Twig_Error_Syntax
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
     * @param User $user
     *
     * @return bool
     */
    public function canAlterUser(User $user): bool
    {
        /** @var Hierarchy $hierarchy */
        $hierarchy = $this->container->get('raisch.user.hierarchy');

        return $hierarchy->canAlterUser($user);
    }

    /**
     * @param User $user
     *
     * @return bool
     */
    public function canDeactivateUser(User $user): bool
    {
        /** @var Hierarchy $hierarchy */
        $hierarchy = $this->container->get('raisch.user.hierarchy');

        return $hierarchy->canDeactivateUser($user);
    }

    /**
     * @param string $date
     * @param string $format
     *
     * @return string
     */
    public function formatDateTimeGitHub(
        string $date,
        string $format = 'd.m.Y H:i:s'
    ): string {
        return DateTime::createFromFormat(DateTime::ISO8601, $date)
            ->format($format);
    }

    /**
     * @param mixed $data
     *
     * @return string
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
