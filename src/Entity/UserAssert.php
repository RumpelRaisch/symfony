<?php

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class UserAssert
 *
 * User entity for forms.
 */
class UserAssert
{
    /**
     * @var string
     *
     * @Assert\Length(min="2", max="50")
     */
    private $name;

    /**
     * @var string
     *
     * @Assert\Length(min="2", max="50")
     */
    private $surname;

    /**
     * @var string
     *
     * @Assert\Length(min="2", max="255")
     */
    private $github_user;

    /**
     * @return null|string
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param null|string $name
     *
     * @return UserAssert
     */
    public function setName(?string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getSurname(): ?string
    {
        return $this->surname;
    }

    /**
     * @param null|string $surname
     *
     * @return UserAssert
     */
    public function setSurname(?string $surname): self
    {
        $this->surname = $surname;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getGithubUser(): ?string
    {
        return $this->github_user;
    }

    /**
     * @param null|string $github_user
     *
     * @return UserAssert
     */
    public function setGithubUser(?string $github_user): self
    {
        $this->github_user = $github_user;

        return $this;
    }
}
