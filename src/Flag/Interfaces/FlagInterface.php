<?php
namespace App\Flag\Interfaces;

interface FlagInterface
{
    /**
     * @param int    $flag
     * @param string $name
     *
     * @return self
     */
    public function setFlag(int $flag, string $name = 'default');

    /**
     * @param int    $flag
     * @param string $name
     *
     * @return self
     */
    public function removeFlag(?int $flag, string $name = 'default');

    /**
     * @param int    $flag
     * @param string $name
     *
     * @return bool
     */
    public function issetFlag(?int $flag, string $name = 'default'): bool;

    /**
     * @param string $name
     *
     * @return int current flags
     */
    public function getFlags(string $name = 'default'): ?int;

    /**
     * @param int    $flags
     * @param string $name
     *
     * @return self
     */
    public function setFlags(int $flags, string $name = 'default');

    /**
     * @param string $name
     *
     * @return bool
     */
    public function issetFlags(string $name = 'default'): bool;

    /**
     * @param string $name
     *
     * @return mixed
     */
    public function unsetFlags(string $name = 'default');
}
