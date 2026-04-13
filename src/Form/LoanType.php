<?php

namespace App\Form;

use App\Entity\Book;
use App\Entity\Loan;
use App\Entity\Member;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;

class LoanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('book', EntityType::class, [
                'label' => 'Livre',
                'class' => Book::class,
                'choice_label' => fn(Book $b) => $b->getTitle() . ' — ' . $b->getAuthor(),
                'constraints' => [new NotBlank()],
                'attr' => ['class' => 'form-select'],
                'query_builder' => fn($repo) => $repo->createQueryBuilder('b')
                    ->where('b.availableCopies > 0')
                    ->orderBy('b.title', 'ASC'),
            ])
            ->add('member', EntityType::class, [
                'label' => 'Adhérent',
                'class' => Member::class,
                'choice_label' => fn(Member $m) => $m->getFullName() . ' (' . $m->getEmail() . ')',
                'constraints' => [new NotBlank()],
                'attr' => ['class' => 'form-select'],
                'query_builder' => fn($repo) => $repo->createQueryBuilder('m')
                    ->where('m.status = :status')
                    ->setParameter('status', 'active')
                    ->orderBy('m.lastName', 'ASC'),
            ])
            ->add('dueDate', DateType::class, [
                'label' => 'Date de retour prévue',
                'widget' => 'single_text',
                'constraints' => [new NotBlank()],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('notes', TextareaType::class, [
                'label' => 'Notes',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 3],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Loan::class]);
    }
}
