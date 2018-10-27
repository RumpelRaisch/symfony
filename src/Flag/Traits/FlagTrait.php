<?php
namespace App\Flag\Traits;

trait FlagTrait
{
    /**
     * @var integer
     */
    protected $flags = 0;

    /**
     * @param int $flag
     *
     * @return self
     */
    public function setFlag(int $flag)
    {
        $this->flags |= $flag;

        return $this;
    }

    /**
     * @param int $flag
     *
     * @return self
     */
    public function removeFlag(int $flag)
    {
        $this->flags &= ~$flag;

        return $this;
    }

    /**
     * @param int $flag
     *
     * @return bool
     */
    public function issetFlag(int $flag): bool
    {
        return (($this->flags & $flag) === $flag);
    }

    /**
     * @return int current flags
     */
    public function getFlags(): int
    {
        return $this->flags;
    }

    /**
     * @param int $flags
     *
     * @return self
     */
    public function setFlags(int $flags)
    {
        $this->flags = $flags;

        return $this;
    }
}
