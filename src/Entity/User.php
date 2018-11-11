<?php
namespace App\Entity;

use App\Kernel;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields="email", message="Email already taken")
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
     * @Assert\NotBlank()
     * @Assert\Email()
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $email;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=4096)
     */
    private $plainPassword;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="json")
     */
    private $roles = [];

    /**
     * @Assert\Length(min="2", max="50")
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $name;

    /**
     * @Assert\Length(min="2", max="50")
     * @ORM\Column(type="string", length=50, nullable=true)
     */
    private $surname;

    /**
     * @Assert\Length(min="2", max="255")
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $github_user;

    /**
     * @Assert\NotBlank(message="Please, upload the avatar as a png or jpeg file.")
     * @Assert\File(mimeTypes={"image/png", "image/jpeg"})
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
     * @var null|\DateTimeInterface
     * @ORM\Column(type="datetime")
     */
    private $created;

    /**
     * @var null|\DateTimeInterface
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     * @ORM\JoinColumn(nullable=false)
     */
    private $created_by;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User")
     */
    private $updated_by;

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
     * {@inheritdoc}
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getSalt()
    {
        // The bcrypt and argon2i algorithms don't require a separate salt.
        // You *may* need a real salt if you choose a different encoder.
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        $this->plainPassword = null;
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
     * @return self
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
     * @return self
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
     * @return self
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
     * @return self
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
        if (null === $this->avatarBase64) {
            if (false === is_resource($this->avatar)) {
                $this->avatarBase64 = base64_encode(file_get_contents(
                    Kernel::AVATAR_DEFAULT_FILE
                ));
            } else {
                $this->avatarBase64 = base64_encode(stream_get_contents(
                    $this->avatar
                ));
            }
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
     * @return self
     */
    public function setTheme(?string $theme): self
    {
        $this->theme = $theme;

        return $this;
    }

    /**
     * @return string
     */
    public function getAvatarMimeType(): string
    {
        return $this->avatar_mime_type ?? Kernel::AVATAR_DEFAULT_MIME;
    }

    /**
     * @param null|string $avatar_mime_type
     *
     * @return self
     */
    public function setAvatarMimeType(?string $avatar_mime_type): self
    {
        $this->avatar_mime_type = $avatar_mime_type;

        return $this;
    }

    /**
     * @return null|string
     */
    public function getPlainPassword(): ?string
    {
        return $this->plainPassword;
    }

    /**
     * @param mixed $plainPassword
     *
     * @return self
     */
    public function setPlainPassword($plainPassword): self
    {
        $this->plainPassword = $plainPassword;

        return $this;
    }

    public function getCreated(): ?\DateTimeInterface
    {
        return $this->created;
    }

    public function getCreatedFormat(string $format = 'd.m.Y H:i:s'): string
    {
        if (false === $this->created instanceof \DateTimeInterface) {
            return '';
        }

        return $this->created->format($format);
    }

    public function setCreated(\DateTimeInterface $created): self
    {
        $this->created = $created;

        return $this;
    }

    public function getUpdated(): ?\DateTimeInterface
    {
        return $this->updated;
    }

    public function getUpdatedFormat(string $format = 'd.m.Y H:i:s'): string
    {
        if (false === $this->updated instanceof \DateTimeInterface) {
            return '';
        }

        return $this->updated->format($format);
    }

    public function setUpdated(\DateTimeInterface $updated): self
    {
        $this->updated = $updated;

        return $this;
    }

    /**
     * Triggered on insert
     *
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->created = new \DateTime('now');
    }

    /**
     * Triggered on update
     *
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updated = new \DateTime('now');
    }

    public function getCreatedBy(): ?self
    {
        return $this->created_by;
    }

    public function setCreatedBy(self $created_by): self
    {
        $this->created_by = $created_by;

        return $this;
    }

    public function getUpdatedBy(): ?self
    {
        return $this->updated_by;
    }

    public function setUpdatedBy(?self $updated_by): self
    {
        $this->updated_by = $updated_by;

        return $this;
    }
}
