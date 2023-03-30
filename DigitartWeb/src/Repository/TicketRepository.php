<?php

namespace App\Repository;

use App\Entity\Ticket;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Ticket>
 *
 * @method Ticket|null find($id, $lockMode = null, $lockVersion = null)
 * @method Ticket|null findOneBy(array $criteria, array $orderBy = null)
 * @method Ticket[]    findAll()
 * @method Ticket[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TicketRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ticket::class);
    }

    public function save(Ticket $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Ticket $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getTicketPrice(string $ticketType, \DateTimeInterface $selectedDate): int
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('t.price')
            ->where('t.ticketType = :ticketType')
            ->andWhere(':selectedDate BETWEEN t.ticketDate AND t.ticketEdate')
            ->setParameter('ticketType', $ticketType)
            ->setParameter('selectedDate', $selectedDate->format('Y-m-d'));

        $result = $qb->getQuery()->getOneOrNullResult();

        if ($result === null) {
            return 0;
        }

        return (int) $result['price'];
    }

    public function getEnabledDates(): array
    {
        $enabledDates = [];
        $tickets = $this->createQueryBuilder('t')
            ->getQuery()
            ->getResult();

        foreach ($tickets as $ticket) {
            $ticketDate = $ticket->getTicketDate();
            $ticketEdate = $ticket->getTicketEdate();
            // Add all dates between ticket_date and ticket_edate to the enabledDates list
            for ($date = $ticketDate; $date <= $ticketEdate; $date->modify('+1 day')) {
                $enabledDates[] = $date->format('Y-m-d');
            }
        }

        return $enabledDates;
    }

    


//    /**
//     * @return Ticket[] Returns an array of Ticket objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('t.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Ticket
//    {
//        return $this->createQueryBuilder('t')
//            ->andWhere('t.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
