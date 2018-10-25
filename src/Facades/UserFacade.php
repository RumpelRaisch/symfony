<?php

namespace App\Facades;

use App\Entity\User;
use App\Entity\UserAssert;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class UserFacade
 * Syncs a user entity and a user entity for forms either way.
 * Saves/updates the user.
 */
class UserFacade
{
    /**
     * @var User
     */
    private $user;

    /**
     * @var UserAssert
     */
    private $userAssert;

    /**
     * @var ObjectManager
     */
    private $manager;

    /**
     * UserFacade constructor.
     *
     * @param User          $user       user entity object
     * @param UserAssert    $userAssert user entity object for forms
     * @param ObjectManager $manager    manager to save/update user
     */
    public function __construct(
        User          $user,
        UserAssert    $userAssert,
        ObjectManager $manager
    ) {
        $this
            ->setUser($user)
            ->setUserAssert($userAssert)
            ->setManager($manager);
    }

    /**
     * Syncs UserAssert with data from User.
     *
     * @return UserFacade
     */
    public function syncUserToUserAssert(): self
    {
        $this->getUserAssert()
            ->setName($this->getUser()->getName())
            ->setSurname($this->getUser()->getSurname())
            ->setGithubUser($this->getUser()->getGithubUser());

        return $this;
    }

    /**
     * Syncs User with data from UserAssert.
     *
     * @return UserFacade
     */
    public function syncUserAssertToUser()
    {
        $this->getUser()
            ->setName($this->getUserAssert()->getName())
            ->setSurname($this->getUserAssert()->getSurname())
            ->setGithubUser($this->getUserAssert()->getGithubUser());

        return $this;
    }

    /**
     * Saves/updates the user.
     */
    public function saveUser()
    {
        $this->getManager()->persist($this->getUser());
        $this->getManager()->flush();
    }

    /**
     * @return null|User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     *
     * @return UserFacade
     */
    protected function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @return null|UserAssert
     */
    public function getUserAssert(): ?UserAssert
    {
        return $this->userAssert;
    }

    /**
     * @param UserAssert $userAssert
     *
     * @return UserFacade
     */
    protected function setUserAssert(UserAssert $userAssert): self
    {
        $this->userAssert = $userAssert;

        return $this;
    }

    /**
     * @return null|ObjectManager
     */
    public function getManager(): ?ObjectManager
    {
        return $this->manager;
    }

    /**
     * @param ObjectManager $manager
     *
     * @return UserFacade
     */
    protected function setManager(ObjectManager $manager): self
    {
        $this->manager = $manager;

        return $this;
    }
}
