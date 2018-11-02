<?php
namespace App\Services\Helper;

use App\Annotations\Sidebar;
use App\Helper\CacheHelper;
use App\Kernel;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Cache\Simple\FilesystemCache;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SidebarHelper
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
final class SidebarHelper
{
    /** @var string */
    private const CACHE_KEY = 'sidebar.items';

    /** @var Kernel */
    private $kernel;

    /** @var ContainerInterface */
    private $container;

    /** @var FilesystemCache */
    private $cache;

    /**
     * SidebarHelper constructor.
     *
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->kernel    = Kernel::getInstance();
        $this->cache     = CacheHelper::getSidebar();
    }

    /**
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * @return array
     */
    public function get(): array
    {
        $items = $this->cache->get(self::CACHE_KEY);

        if (true === empty($items)) {
            $items = $this->buildSidebarItemTree();

            $this->cache->set(self::CACHE_KEY, $items);
        }

        return $items;
    }

    /**
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *
     * @return array
     */
    public function reload(): array
    {
        $this->cache->delete(self::CACHE_KEY);

        return $this->get();
    }

    /**
     * @return array
     */
    private function buildSidebarItemTree(): array
    {
        $sidebarItems = $this->container
            ->get('raisch.sidebar.annotations_reader')
            ->read();

        $parents  = [];
        $children = [];

        foreach ($sidebarItems as $sidebarItem) {
            $isGrantedClass = [];

            foreach ($sidebarItem['annotations'] as $annotation) {
                if ($annotation instanceof IsGranted) {
                    $isGrantedClass[] = $annotation->getAttributes();
                }
            }

            foreach ($sidebarItem['methods'] as $method) {
                // first route = main route => ignore rest
                /** @var Route $route */
                $route           = array_pop($method['annotations']['Route']);
                $isGrantedMethod = [];

                if (false === empty($method['annotations']['IsGranted'])) {
                    /** @var IsGranted $isGranted */
                    foreach ($method['annotations']['IsGranted'] as $isGranted) {
                        $isGrantedMethod[] = $isGranted->getAttributes();
                    }
                }

                $isGrantedMethod += $isGrantedClass;
                $isGrantedMethod  = array_unique($isGrantedMethod);

                /** @var Sidebar $sidebar */
                foreach ($method['annotations']['Sidebar'] as $sidebar) {
                    if (true === empty($sidebar->getParent())) {
                        $ref = &$parents;
                    } else {
                        $ref = &$children;
                    }

                    $ref[$sidebar->getName()] = [
                        'name'       => $sidebar->getName(),
                        'icon'       => $sidebar->getIcon(),
                        'position'   => $sidebar->getPosition() ?? 99999,
                        'parent'     => $sidebar->getParent(),
                        'controller' => $sidebarItem['controllerName'],
                        'isGranted'  => $isGrantedMethod,
                        'route'      => [
                            'path'     => $route->getName(),
                            'defaults' => $route->getDefaults(),
                        ],
                        'children'   => [],
                    ];
                }
            }
        }

        foreach ($children as $child) {
            if (true === isset($parents[$child['parent']])) {
                $parents[$child['parent']]['children'][] = $child;
            }
        }

        usort($parents, [$this, 'usort']);

        foreach ($parents as &$parent) {
            unset($parent['parent'], $parent['position']);

            if (false === empty($parent['children'])) {
                unset($parent['route']);
                usort($parent['children'], [$this, 'usort']);
            }

            foreach ($parent['children'] as &$child) {
                unset(
                    $child['children'],
                    $child['controller'],
                    $child['parent'],
                    $child['position']
                );
            }
        }

        return $parents;
    }

    /**
     * @param array $a
     * @param array $b
     *
     * @return int
     */
    private function usort(array $a, array $b)
    {
        return $a['position'] <=> $b['position'];
    }
}
