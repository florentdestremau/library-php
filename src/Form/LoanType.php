<?php

namespace App\Form;

use App\Entity\Loan;
use App\Form\Field\BookAutocompleteField;
use App\Form\Field\MemberAutocompleteField;
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
            ->add('book', BookAutocompleteField::class, [
                'label' => 'Livre',
                'constraints' => [new NotBlank()],
            ])
            ->add('member', MemberAutocompleteField::class, [
                'label' => 'Adhérent',
                'constraints' => [new NotBlank()],
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
