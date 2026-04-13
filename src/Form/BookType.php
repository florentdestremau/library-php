<?php

namespace App\Form;

use App\Entity\Book;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class BookType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'constraints' => [new NotBlank(), new Length(max: 255)],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('author', TextType::class, [
                'label' => 'Auteur',
                'constraints' => [new NotBlank(), new Length(max: 255)],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('isbn', TextType::class, [
                'label' => 'ISBN',
                'required' => false,
                'constraints' => [new Length(max: 13)],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('genre', TextType::class, [
                'label' => 'Genre',
                'constraints' => [new NotBlank(), new Length(max: 100)],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('publicationYear', IntegerType::class, [
                'label' => 'Année de publication',
                'constraints' => [new NotBlank(), new Range(min: 1000, max: 2100)],
                'attr' => ['class' => 'form-control'],
            ])
            ->add('description', TextareaType::class, [
                'label' => 'Description',
                'required' => false,
                'attr' => ['class' => 'form-control', 'rows' => 4],
            ])
            ->add('totalCopies', IntegerType::class, [
                'label' => 'Nombre d\'exemplaires',
                'constraints' => [new NotBlank(), new Range(min: 1)],
                'attr' => ['class' => 'form-control'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults(['data_class' => Book::class]);
    }
}
