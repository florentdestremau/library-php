<?php

namespace App\Twig\Components;

use App\Repository\LoanRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class LoanSearch
{
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: true)]
    public string $query = '';

    #[LiveProp(writable: true, url: true)]
    public string $status = '';

    #[LiveProp(writable: true, url: true)]
    public int $page = 1;

    public function __construct(
        private readonly LoanRepository $loanRepository,
        private readonly PaginatorInterface $paginator,
    ) {}

    public function getLoans(): \Knp\Component\Pager\Pagination\PaginationInterface
    {
        $qb = $this->loanRepository->createSearchQueryBuilder(
            $this->query,
            $this->status,
        );

        return $this->paginator->paginate($qb, $this->page, 15);
    }
}
