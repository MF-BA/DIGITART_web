<?php

namespace App\Repository;

use App\Entity\Artwork;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Artwork>
 *
 * @method Artwork|null find($id, $lockMode = null, $lockVersion = null)
 * @method Artwork|null findOneBy(array $criteria, array $orderBy = null)
 * @method Artwork[]    findAll()
 * @method Artwork[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ArtworkRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Artwork::class);
    }

    public function save(Artwork $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Artwork $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function searchArtworkWithRoom(int $roomId): bool
{
    $artwork = $this->getEntityManager()->getRepository(Artwork::class)->findOneBy(['idRoom' => $roomId]);

    return ($artwork !== null);
}


public function countArtworks()
{
    return $this->getEntityManager()
        ->createQuery('SELECT COUNT(a.idArt) FROM App\Entity\Artwork a')
        ->getSingleScalarResult();
}
public function countArtworksByDecade(): array
{
    $qb = $this->getEntityManager()->createQueryBuilder();
    $qb->select('CONCAT(FLOOR(YEAR(a.date) / 10) * 10, "s") AS decade, COUNT(a.id) AS artworkCount')
       ->from('App\Entity\Artwork', 'a')
       ->groupBy('decade');

    $results = $qb->getQuery()->getResult();

    $countByDecade = [];
    foreach ($results as $result) {
        $countByDecade[$result['decade']] = $result['artworkCount'];
    }

    return $countByDecade;
}

public function getArtworksPerRoom()
{
    $queryBuilder = $this->getEntityManager()
        ->createQueryBuilder()
        ->select('r.nameRoom, COUNT(a.idArt) as artworksCount')
        ->from('App\Entity\Room', 'r')
        ->leftJoin('App\Entity\Artwork', 'a', 'WITH', 'a.idRoom = r.idRoom')
        ->groupBy('r.idRoom')
        ->having('artworksCount > 0');
    
    return $queryBuilder->getQuery()->getResult();
}


public function findLastCreatedArtwork()
{
    return $this->createQueryBuilder('a')
        ->orderBy('a.createdAt', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
}

public function findLastUpdatedArtwork()
{
    return $this->createQueryBuilder('a')
        ->orderBy('a.updatedAt', 'DESC')
        ->setMaxResults(1)
        ->getQuery()
        ->getOneOrNullResult();
}


public function findByRoomName($selectedRoom)
{
    return $this->createQueryBuilder('a')
        ->join('a.room', 'r')
        ->andWhere('r.name = :roomName')
        ->setParameter('roomName', $selectedRoom)
        ->getQuery()
        ->getResult();
}



//    /**
//     * @return Artwork[] Returns an array of Artwork objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('a.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Artwork
//    {
//        return $this->createQueryBuilder('a')
//            ->andWhere('a.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
