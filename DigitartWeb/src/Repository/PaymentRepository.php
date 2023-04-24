<?php

namespace App\Repository;

use App\Entity\Payment;
use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Payment>
 *
 * @method Payment|null find($id, $lockMode = null, $lockVersion = null)
 * @method Payment|null findOneBy(array $criteria, array $orderBy = null)
 * @method Payment[]    findAll()
 * @method Payment[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PaymentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Payment::class);
    }

    public function save(Payment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Payment $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getPaymentsByUserId(int $userId): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->where('p.user_id = :userId')
            ->andWhere('p.paid = :paid')
            ->andWhere('p.purchaseDate >= :today')
            ->setParameter('userId', $userId)
            ->setParameter('paid', true)
            ->setParameter('today', new \DateTime());
        
        return $queryBuilder->getQuery()->getResult();
    }

    public function getLastUpdatedAtByUserId()
    {
        $qb = $this->createQueryBuilder('p')
            ->select('MAX(p.updatedAt)');

    
        $result = $qb->getQuery()->getSingleScalarResult();
    
        return $result;
    }

    
    public function getTotalAdult()
    {
        $qb = $this->createQueryBuilder('p')
            ->select('SUM(p.nbAdult) as totalAdult')
            ->getQuery();

        return $qb->getSingleScalarResult();
    }

    public function getTotalTeenager()
    {
        $qb = $this->createQueryBuilder('p')
            ->select('SUM(p.nbTeenager) as totalTeenager')
            ->getQuery();

        return $qb->getSingleScalarResult();
    }

    public function getTotalStudent()
    {
        $qb = $this->createQueryBuilder('p')
            ->select('SUM(p.nbStudent) as totalStudent')
            ->getQuery();

        return $qb->getSingleScalarResult();
    }

    public function paginationQuery()
    {
        return $this->createQueryBuilder('p')
            ->orderBy('p.paymentId', 'ASC') // Update to use the correct field name
            ->getQuery();
    }
    
    public function getTotalPaymentByPurchaseDate(): array
    {
        return $this->createQueryBuilder('p')
            ->select('p.purchaseDate as purchase_date, SUM(p.totalPayment) as total_payment')
            ->groupBy('p.purchaseDate')
            ->getQuery()
            ->getResult();
    }
//    /**
//     * @return Payment[] Returns an array of Payment objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('p.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Payment
//    {
//        return $this->createQueryBuilder('p')
//            ->andWhere('p.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
