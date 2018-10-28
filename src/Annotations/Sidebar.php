<?php
namespace App\Annotations;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class Sidebar
 *
 * Sidebar items with parent cant have children.
 * Tree builder will ignore the childrens in this case.
 *
 * @author Rainer Schulz <rainer.schulz@bitshifting.de>
 *
 * @Annotation
 * @Target("METHOD")
 */
final class Sidebar
{
    /** @var string */
    public $name;

    /** @var string */
    public $icon;

    /** @var int */
    public $position;

    /** @var string */
    public $parent;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getIcon(): string
    {
        return $this->icon;
    }

    /**
     * @return null|int
     */
    public function getPosition(): ?int
    {
        return $this->position;
    }

    /**
     * @return null|string
     */
    public function getParent(): ?string
    {
        return $this->parent;
    }
}
