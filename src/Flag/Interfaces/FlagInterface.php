<?php
namespace App\Flag\Interfaces;

interface FlagInterface
{
    /**
     * @param int $flag
     *
     * @return self
     */
    public function setFlag(int $flag);

    /**
     * @param int $flag
     *
     * @return self
     */
    public function removeFlag(int $flag);

    /**
     * @param int $flag
     *
     * @return bool
     */
    public function issetFlag(int $flag): bool;

    /**
     * @return int current flags
     */
    public function getFlags(): int;

    /**
     * @param int $flags
     *
     * @return self
     */
    public function setFlags(int $flags);
}
