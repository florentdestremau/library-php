<?php

namespace App\Form\Field;

use App\Entity\Member;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\UX\Autocomplete\Form\AsEntityAutocompleteField;
use Symfony\UX\Autocomplete\Form\BaseEntityAutocompleteType;

#[AsEntityAutocompleteField]
class MemberAutocompleteField extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'class' => Member::class,
            'searchable_fields' => ['firstName', 'lastName', 'email'],
            'choice_label' => fn(Member $member) => sprintf(
                '%s · %s',
                $member->getFullName(),
                $member->getEmail(),
            ),
            'query_builder' => fn(EntityRepository $repo): QueryBuilder => $repo
                ->createQueryBuilder('m')
                ->where('m.status = :status')
                ->setParameter('status', 'active')
                ->orderBy('m.lastName', 'ASC'),
            'placeholder' => 'Rechercher un adhérent actif...',
            'no_results_found_text' => 'Aucun adhérent trouvé',
            'tom_select_options' => [
                'highlight' => true,
                'shouldLoad' => 'function(query) { return query.length >= 2; }',
            ],
        ]);
    }

    public function getParent(): string
    {
        return BaseEntityAutocompleteType::class;
    }
}
