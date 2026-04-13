<?php

namespace App\Form\Field;

use App\Entity\Book;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class BookAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Book::class,
            'searchable_fields' => ['title', 'author', 'isbn'],
            'choice_label' => fn(Book $book) => sprintf(
                '%s — %s (%d) · %d dispo',
                $book->getTitle(),
                $book->getAuthor(),
                $book->getPublicationYear(),
                $book->getAvailableCopies(),
            ),
            'query_builder' => fn(EntityRepository $repo): QueryBuilder => $repo
                ->createQueryBuilder('b')
                ->where('b.availableCopies > 0')
                ->orderBy('b.title', 'ASC'),
            'placeholder' => 'Rechercher un livre disponible...',
            'no_results_found_text' => 'Aucun livre disponible trouvé',
            'tom_select_options' => [
                'highlight' => true,
                'shouldLoad' => 'function(query) { return query.length >= 2; }',
            ],
            'attr' => ['data-min-chars' => 2],
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
