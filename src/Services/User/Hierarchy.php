<?php
namespace App\Services\User;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use Doctrine\Common\Persistence\ManagerRegistry;
use RuntimeException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class Hierarchy
{
    /** @var ContainerInterface */
    private $container;

    /** @var AuthorizationChecker */
    private $auth;

    /** @var User */
    private $user;

    /** @var ManagerRegistry */
    private $orm;

    /** @var RoleRepository */
    private $roleRepo;

    /**
     * Hierarchy constructor.
     *
     * @param ContainerInterface    $container
     * @param TokenStorageInterface $token
     * @param AuthorizationChecker  $auth
     */
    public function __construct(
        ContainerInterface    $container,
        TokenStorageInterface $token,
        AuthorizationChecker  $auth
    ) {
        $this->container = $container;
        $this->auth      = $auth;
        $this->user      = $token->getToken()->getUser();
        $this->orm       = $this->container->get('doctrine');
        $this->roleRepo  = $this->orm->getRepository(Role::class);
    }

    /**
     * @return string[]
     */
    public function getAssignableRoles(): array
    {
        /** @var Role[] $roles */
        $roles = $this->roleRepo
            ->createQueryBuilder('r')
            ->orderBy('r.name', 'ASC')
            ->getQuery()
            ->getResult();

        $authRoles = [];

        foreach ($roles as $role) {
            if ($this->auth->isGranted($role->getIsGranted())) {
                $authRoles[$role->getName()] = $role->getName();
            }
        }

        return $authRoles;
    }

    /**
     * @param User $user
     *
     * @throws RuntimeException
     *
     * @return bool
     */
    public function canAlterUser(User $user): bool
    {
        if ($this->user->getId() === $user->getId()) {
            return true;
        }

        return $this->isGrantedToAlterUser($user);
    }

    /**
     * @param User $user
     *
     * @throws RuntimeException
     *
     * @return bool
     */
    public function canDeactivateUser(User $user): bool
    {
        if ($this->user->getId() === $user->getId()) {
            return false;
        }

        return $this->isGrantedToAlterUser($user);
    }

    /**
     * @param User $user
     *
     * @throws RuntimeException
     *
     * @return bool
     */
    protected function isGrantedToAlterUser(User $user): bool
    {
        foreach ($user->getRoles() as $roleName) {
            $role = $this->roleRepo->findOneByName($roleName);

            if (null === $role) {
                throw new RuntimeException('Unidentified Role.', 500);
            }

            if (false === $this->auth->isGranted($role->getIsGranted())) {
                return false;
            }
        }

        return true;
    }
}
