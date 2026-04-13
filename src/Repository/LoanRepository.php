<?php

namespace App\Repository;

use App\Entity\Loan;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Loan>
 */
class LoanRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Loan::class);
    }

    public function createSearchQueryBuilder(string $query = '', string $status = ''): QueryBuilder
    {
        $qb = $this->createQueryBuilder('l')
            ->join('l.book', 'b')
            ->join('l.member', 'm')
            ->addSelect('b', 'm');

        if ($query !== '') {
            $qb->andWhere(
                'b.title LIKE :query OR m.firstName LIKE :query OR m.lastName LIKE :query OR m.email LIKE :query'
            )->setParameter('query', '%' . $query . '%');
        }

        if ($status !== '') {
            $qb->andWhere('l.status = :status')
               ->setParameter('status', $status);
        }

        return $qb->orderBy('l.borrowedAt', 'DESC');
    }

    public function countActive(): int
    {
        return $this->count(['status' => 'borrowed']);
    }

    public function countOverdue(): int
    {
        return (int) $this->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->where('l.status = :status')
            ->andWhere('l.dueDate < :now')
            ->setParameter('status', 'borrowed')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findActiveByMember(int $memberId): array
    {
        return $this->createQueryBuilder('l')
            ->join('l.book', 'b')
            ->addSelect('b')
            ->where('l.member = :memberId')
            ->andWhere('l.status = :status')
            ->setParameter('memberId', $memberId)
            ->setParameter('status', 'borrowed')
            ->orderBy('l.dueDate', 'ASC')
            ->getQuery()
            ->getResult();
    }

    public function findActiveByBook(int $bookId): array
    {
        return $this->createQueryBuilder('l')
            ->join('l.member', 'm')
            ->addSelect('m')
            ->where('l.book = :bookId')
            ->andWhere('l.status = :status')
            ->setParameter('bookId', $bookId)
            ->setParameter('status', 'borrowed')
            ->orderBy('l.borrowedAt', 'DESC')
            ->getQuery()
            ->getResult();
    }
}
