<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $name;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $surname;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $github_user;

    /**
     * @ORM\Column(type="blob", nullable=true)
     */
    private $avatar;

    /**
     * @var null|string
     */
    private $avatarBase64 = null;

    /**
     * @ORM\Column(type="string", length=25, nullable=true)
     */
    private $theme;

    /**
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $avatar_mime_type;

    /**
     * @return null|int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return null|string
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * @param string $email
     *
     * @return User
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * @inheritdoc
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param array $roles
     *
     * @return User
     */
    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getPassword(): string
    {
        return (string) $this->password;
    }

    /**
     * @param string $password
     *
     * @return User
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getSalt()
    {
        // not needed when using the "bcrypt" algorithm in security.yaml
    }

    /**
     * @inheritdoc
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

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
     * @return User
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
     * @return User
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
     * @return User
     */
    public function setGithubUser(?string $github_user): self
    {
        $this->github_user = $github_user;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getAvatar()
    {
        return $this->avatar;
    }

    /**
     * @param $avatar
     *
     * @return User
     */
    public function setAvatar($avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    /**
     * @return string
     */
    public function getAvatarBase64(): string
    {
        if (false === is_resource($this->avatar)) {
            return ''; // TODO: return default avatar
        }

        if (null === $this->avatarBase64) {
            $this->avatarBase64 = base64_encode(stream_get_contents($this->avatar));
        }

        return $this->avatarBase64;
    }

    /**
     * @return null|string
     */
    public function getTheme(): ?string
    {
        return $this->theme;
    }

    /**
     * @param null|string $theme
     *
     * @return User
     */
    public function setTheme(?string $theme): self
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getAvatarMimeType(): ?string
    {
        return $this->avatar_mime_type;
    }

    /**
     * @param null|string $avatar_mime_type
     *
     * @return User
     */
    public function setAvatarMimeType(?string $avatar_mime_type): self
    {
        $this->avatar_mime_type = $avatar_mime_type;

        return $this;
    }
}
