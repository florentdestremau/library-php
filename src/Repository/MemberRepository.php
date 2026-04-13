<?php

namespace App\Repository;

use App\Entity\Member;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Member>
 */
class MemberRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Member::class);
    }

    public function createSearchQueryBuilder(string $query = '', string $status = ''): QueryBuilder
    {
        $qb = $this->createQueryBuilder('m');

        if ($query !== '') {
            $qb->andWhere('m.firstName LIKE :query OR m.lastName LIKE :query OR m.email LIKE :query')
               ->setParameter('query', '%' . $query . '%');
        }

        if ($status !== '') {
            $qb->andWhere('m.status = :status')
               ->setParameter('status', $status);
        }

        return $qb->orderBy('m.lastName', 'ASC')->addOrderBy('m.firstName', 'ASC');
    }

    public function countActive(): int
    {
        return $this->count(['status' => 'active']);
    }
}
