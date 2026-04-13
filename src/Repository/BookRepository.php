<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function createSearchQueryBuilder(string $query = '', string $genre = '', string $availability = ''): QueryBuilder
    {
        $qb = $this->createQueryBuilder('b');

        if ($query !== '') {
            $qb->andWhere('b.title LIKE :query OR b.author LIKE :query OR b.isbn LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }

        if ($genre !== '') {
            $qb->andWhere('b.genre = :genre')
               ->setParameter('genre', $genre);
        }

        if ($availability === 'available') {
            $qb->andWhere('b.availableCopies > 0');
        } elseif ($availability === 'unavailable') {
            $qb->andWhere('b.availableCopies = 0');
        }

        return $qb->orderBy('b.title', 'ASC');
    }

    public function findAllGenres(): array
    {
        return $this->createQueryBuilder('b')
            ->select('DISTINCT b.genre')
            ->orderBy('b.genre', 'ASC')
            ->getQuery()
            ->getSingleColumnResult();
    }

    public function countAvailable(): int
    {
        return (int) $this->createQueryBuilder('b')
            ->select('SUM(b.availableCopies)')
            ->getQuery()
            ->getSingleScalarResult();
    }
}
