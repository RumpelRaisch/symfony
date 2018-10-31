<?php
namespace App\Flag\Traits;

trait FlagTrait
{
    /**
     * @var int[]
     */
    private $flags = [];

    /**
     * @param int    $flag
     * @param string $name
     *
     * @return self
     */
    public function setFlag(int $flag, string $name = 'default')
    {
        if (false === isset($this->flags[$name])) {
            $this->flags[$name] = 0;
        }

        $this->flags[$name] |= $flag;

        return $this;
    }

    /**
     * @param int    $flag
     * @param string $name
     *
     * @return self
     */
    public function removeFlag(?int $flag, string $name = 'default')
    {
        if (null !== $flag && true === isset($this->flags[$name])) {
            $this->flags[$name] &= ~$flag;
        }

        return $this;
    }

    /**
     * @param int    $flag
     * @param string $name
     *
     * @return bool
     */
    public function issetFlag(?int $flag, string $name = 'default'): bool
    {
        if (null !== $flag && false === isset($this->flags[$name])) {
            return false;
        }

        return (($this->flags[$name] & $flag) === $flag);
    }

    /**
     * @param string $name
     *
     * @return null|int current flags
     */
    public function getFlags(string $name = 'default'): ?int
    {
        if (false === isset($this->flags[$name])) {
            return null;
        }

        return $this->flags[$name];
    }

    /**
     * @param int    $flags
     * @param string $name
     *
     * @return self
     */
    public function setFlags(int $flags, string $name = 'default')
    {
        $this->flags[$name] = $flags;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function issetFlags(string $name = 'default'): bool
    {
        return isset($this->flags[$name]);
    }

    /**
     * @param string $name
     *
     * @return $this
     */
    public function unsetFlags(string $name = 'default')
    {
        if (true === isset($this->flags[$name])) {
            unset($this->flags[$name]);
        }

        return $this;
    }
}
