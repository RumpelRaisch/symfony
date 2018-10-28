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
     */
    public function read(): array
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

        return $this->sidebarItems;
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
    public function getSidebarItems(): array
    {
        return $this->sidebarItems;
    }
}
