<?php

namespace App\Repository;

use App\Entity\Users;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Func;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<Users>
 *
 * @method Users|null find($id, $lockMode = null, $lockVersion = null)
 * @method Users|null findOneBy(array $criteria, array $orderBy = null)
 * @method Users[]    findAll()
 * @method Users[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UsersRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Users::class);
    }

    public function search(string $mots = null ,string $roles = null): array
    {
        $query = $this->createQueryBuilder('u');
        
        if($mots !== null){
           
                $query->andWhere('u.firstname LIKE :mots or u.lastname LIKE :mots or u.email LIKE :mots or u.cin LIKE :mots or u.address LIKE :mots or u.phoneNum LIKE :mots')
                ->setParameter('mots', '%' . $mots . '%');
        } 
        if ($roles !== null)
        {
           $query->andWhere('u.role LIKE :roles')
           ->setParameter('roles', '%' . $roles . '%');
        }
        return $query->getQuery()->getResult();
    }

    public function getuserNameById($id)
    {
        $user = $this->findOneBy(['id' => $id]);
        return $user ? $user->getLastname() : null;
    }
    
    public function save(Users $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Users $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof Users) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->save($user, true);
    }


    public function findOneByEmail(string $email): ?Users
{
    return $this->createQueryBuilder('u')
        ->andWhere('u.email = :email')
        ->setParameter('email', $email)
        ->getQuery()
        ->getOneOrNullResult()
    ;
}


public function getPaginatedusers($page, $limit, $filters = null){
    $query = $this->createQueryBuilder('u');
   /* ->where('u.is_verified = 1');*/
       

    // On filtre les données
    if($filters != null){
        $query->andWhere('u.role IN(:Subscriber)')
            ->setParameter(':Subscriber', array_values($filters));
    }

    $query->orderBy('u.createdAt')
        ->setFirstResult(($page * $limit) - $limit)
        ->setMaxResults($limit)
    ;
    return $query->getQuery()->getResult();
}

public function getTotalUsers($filters){
    $query = $this->createQueryBuilder('u')
            ->select('COUNT(u)');
            /*->where('u.is_verified = 1');*/
        // On filtre les données
        if($filters != null){
            $query->andWhere('u.role IN(:Subscriber)')
                ->setParameter(':Subscriber', array_values($filters));
        }

        return $query->getQuery()->getSingleScalarResult();
}


//    /**
//     * @return Users[] Returns an array of Users objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Users
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
