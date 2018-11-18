<?php
namespace App\Repository;

use App\Entity\Role;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method null|Role find($id, $lockMode = null, $lockVersion = null)
 * @method null|Role findOneBy(array $criteria, array $orderBy = null)
 * @method Role[]    findAll()
 * @method Role[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class RoleRepository extends ServiceEntityRepository
{
    /**
     * RoleRepository constructor.
     *
     * @param RegistryInterface $registry
     */
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Role::class);
    }

    /**
     * @param string $name
     *
     * @return null|Role
     */
    public function findOneByName(string $name): ?Role
    {
        try {
            return $this->createQueryBuilder('r')
                ->andWhere('r.name = :name')
                ->setParameter('name', $name)
                ->getQuery()
                ->getOneOrNullResult();
        } catch (NonUniqueResultException $ex) {
            return null;
        }
    }

    // /**
    //  * @param mixed $value
    //  *
    //  * @return Role[] Returns an array of Role objects
    //  */
    // public function findByExampleField($value)
    // {
    //     return $this->createQueryBuilder('r')
    //         ->andWhere('r.exampleField = :val')
    //         ->setParameter('val', $value)
    //         ->orderBy('r.id', 'ASC')
    //         ->setMaxResults(10)
    //         ->getQuery()
    //         ->getResult()
    //     ;
    // }
    //
    // /**
    //  * @param $value
    //  *
    //  * @throws NonUniqueResultException
    //  *
    //  * @return null|Role
    //  */
    // public function findOneBySomeField($value): ?Role
    // {
    //     return $this->createQueryBuilder('r')
    //         ->andWhere('r.exampleField = :val')
    //         ->setParameter('val', $value)
    //         ->getQuery()
    //         ->getOneOrNullResult()
    //     ;
    // }
}
