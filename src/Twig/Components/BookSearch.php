<?php

namespace App\Twig\Components;

use App\Repository\BookRepository;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\UX\LiveComponent\Attribute\AsLiveComponent;
use Symfony\UX\LiveComponent\Attribute\LiveProp;
use Symfony\UX\LiveComponent\DefaultActionTrait;

#[AsLiveComponent]
final class BookSearch
{
    use DefaultActionTrait;

    #[LiveProp(writable: true, url: true)]
    public string $query = '';

    #[LiveProp(writable: true, url: true)]
    public string $genre = '';

    #[LiveProp(writable: true, url: true)]
    public string $availability = '';

    #[LiveProp(writable: true, url: true)]
    public int $page = 1;

    public function __construct(
        private readonly BookRepository $bookRepository,
        private readonly PaginatorInterface $paginator,
    ) {}

    public function getBooks(): \Knp\Component\Pager\Pagination\PaginationInterface
    {
        $qb = $this->bookRepository->createSearchQueryBuilder(
            $this->query,
            $this->genre,
            $this->availability,
        );

        return $this->paginator->paginate($qb, $this->page, 12);
    }

    public function getGenres(): array
    {
        return $this->bookRepository->findAllGenres();
    }
}
