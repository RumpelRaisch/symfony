<?php
namespace App\Services\Annotations;

use \ReflectionClass;
use \ReflectionException;
use \ReflectionMethod;
use App\Annotations\Sidebar;
use Doctrine\Common\Annotations\Reader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class SidebarAnnotationsReader
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 */
final class SidebarAnnotationsReader
{
    /** @var array */
    private $sidebarItems = [];

    /** @var Reader */
    private $reader;

    /** @var KernelInterface */
    private $kernel;

    /** @var array */
    private $context;

    /**
     * SidebarAnnotationsReader constructor.
     *
     * @param KernelInterface $kernel
     * @param Reader          $reader
     */
    public function __construct(KernelInterface $kernel, Reader $reader)
    {
        $this->reader  = $reader;
        $this->kernel  = $kernel;
        $this->context = ['__AREA__' => 'SidebarAnnotationsReader'];
    }

    /**
     * @return array
     *
     * TODO: cache sidebar tree and provide way to clear cache
     */
    public function getTree(): array
    {
        $path   = $this->kernel->getRootDir() . '/../src/Controller';
        $finder = new Finder();

        $finder->files()->in($path);

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $class = 'App\\Controller\\' . $file->getBasename('.php');

            try {
                $reflectionClass = new ReflectionClass($class);
            } catch (ReflectionException $ex) {
                continue;
            }

            /** @var ReflectionMethod $reflectionMethod */
            foreach ($reflectionClass->getMethods() as $reflectionMethod) {
                $annotations = $this->reader->getMethodAnnotations(
                    $reflectionMethod
                );

                $annotations = $this->filterAnnotations(
                    $annotations,
                    [
                        'Route'     => Route::class,
                        'Sidebar'   => Sidebar::class,
                        'IsGranted' => IsGranted::class,
                    ],
                    ['Route', 'Sidebar']
                );

                if (true === empty($annotations)) {
                    continue;
                }

                if (false === isset($this->sidebarItems[$class])) {
                    $this->sidebarItems[$class] = [
                        'annotations'    => $this->reader->getClassAnnotations(
                            $reflectionClass
                        ),
                        'controllerName' => $reflectionClass->getConstant(
                            'CONTROLLER_NAME'
                        ),
                        'methods'        => [],
                    ];
                }

                $this->sidebarItems[$class]['methods'][$reflectionMethod->getName()] = [
                    'class'       => $class,
                    'annotations' => $annotations,
                ];
            }
        }

        return $this->buildSidebarItemTree();
    }

    /**
     * @param array $annotations
     * @param array $classes
     * @param array $requiered
     *
     * @return null|array
     */
    private function filterAnnotations(
        array $annotations,
        array $classes,
        array $requiered
    ): ?array {
        $filtered = [];

        foreach ($annotations as $annotation) {
            foreach ($classes as $name => $fqcn) {
                if ($annotation instanceof $fqcn) {
                    if (false === isset($filtered[$name])) {
                        $filtered[$name] = [];
                    }

                    $filtered[$name][] = $annotation;

                    continue;
                }
            }
        }

        foreach ($requiered as $name) {
            if (false === isset($filtered[$name])) {
                return null;
            }
        }

        return $filtered;
    }

    /**
     * @return array
     */
    private function buildSidebarItemTree(): array
    {
        $parents  = [];
        $children = [];

        foreach ($this->sidebarItems as $sidebarItem) {
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

                $isGrantedMethod = $isGrantedClass + $isGrantedMethod;
                $isGrantedMethod = array_unique($isGrantedMethod);

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
