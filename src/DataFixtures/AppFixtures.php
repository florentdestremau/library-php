<?php

namespace App\DataFixtures;

use App\Entity\Book;
use App\Entity\Loan;
use App\Entity\Member;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AppFixtures extends Fixture
{
    private const GENRES = [
        'Roman', 'Science-Fiction', 'Fantasy', 'Policier', 'Thriller',
        'Biographie', 'Histoire', 'Sciences', 'Philosophie', 'Poésie',
        'Jeunesse', 'Bande Dessinée', 'Art', 'Cuisine', 'Voyage',
        'Développement personnel', 'Économie', 'Droit', 'Médecine', 'Informatique',
    ];

    private const FAMOUS_AUTHORS = [
        'Victor Hugo', 'Émile Zola', 'Gustave Flaubert', 'Albert Camus', 'Marcel Proust',
        'Simone de Beauvoir', 'Jean-Paul Sartre', 'Jules Verne', 'Alexandre Dumas',
        'Honoré de Balzac', 'Stendhal', 'George Orwell', 'Franz Kafka', 'Virginia Woolf',
        'Ernest Hemingway', 'Fyodor Dostoevsky', 'Leo Tolstoy', 'Charles Dickens',
        'Gabriel García Márquez', 'Haruki Murakami',
    ];

    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        $faker->seed(42);

        $books = $this->loadBooks($manager, $faker);
        $members = $this->loadMembers($manager, $faker);
        $manager->flush();

        $this->loadLoans($manager, $faker, $books, $members);
        $manager->flush();
    }

    private function loadBooks(ObjectManager $manager, \Faker\Generator $faker): array
    {
        $books = [];

        for ($i = 0; $i < 500; $i++) {
            $book = new Book();
            $totalCopies = $faker->numberBetween(1, 5);

            $book->setTitle($this->generateBookTitle($faker))
                 ->setAuthor($faker->randomElement(self::FAMOUS_AUTHORS))
                 ->setIsbn($faker->isbn13())
                 ->setGenre($faker->randomElement(self::GENRES))
                 ->setPublicationYear($faker->numberBetween(1900, 2024))
                 ->setDescription($faker->optional(0.7)->paragraph(3))
                 ->setTotalCopies($totalCopies)
                 ->setAvailableCopies($totalCopies);

            $manager->persist($book);
            $books[] = $book;
        }

        return $books;
    }

    private function loadMembers(ObjectManager $manager, \Faker\Generator $faker): array
    {
        $members = [];
        $emails = [];

        for ($i = 0; $i < 200; $i++) {
            $member = new Member();
            $membershipDate = $faker->dateTimeBetween('-3 years', 'now');

            do {
                $email = $faker->email();
            } while (in_array($email, $emails));
            $emails[] = $email;

            $status = $faker->randomElement(['active', 'active', 'active', 'suspended', 'expired']);

            $member->setFirstName($faker->firstName())
                   ->setLastName($faker->lastName())
                   ->setEmail($email)
                   ->setPhone($faker->optional(0.8)->phoneNumber())
                   ->setBirthDate($faker->dateTimeBetween('-80 years', '-18 years'))
                   ->setMembershipDate($membershipDate)
                   ->setMembershipExpiry($faker->dateTimeBetween('now', '+2 years'))
                   ->setStatus($status);

            $manager->persist($member);
            $members[] = $member;
        }

        return $members;
    }

    private function loadLoans(ObjectManager $manager, \Faker\Generator $faker, array $books, array $members): void
    {
        $activeMembers = array_values(array_filter($members, fn(Member $m) => $m->getStatus() === 'active'));

        // Past returned loans
        for ($i = 0; $i < 300; $i++) {
            $book = $faker->randomElement($books);
            $member = $faker->randomElement($activeMembers);

            $borrowedAt = $faker->dateTimeBetween('-2 years', '-1 month');
            $dueDate = (clone $borrowedAt)->modify('+21 days');
            $returnedAt = $faker->dateTimeBetween($borrowedAt, '+10 days');

            $loan = new Loan();
            $loan->setBook($book)
                 ->setMember($member)
                 ->setBorrowedAt($borrowedAt)
                 ->setDueDate($dueDate)
                 ->setReturnedAt($returnedAt)
                 ->setStatus('returned');

            $manager->persist($loan);
        }

        // Active loans
        $activeLoanCount = 0;
        $shuffledBooks = $books;
        shuffle($shuffledBooks);

        foreach ($shuffledBooks as $book) {
            if ($activeLoanCount >= 60) {
                break;
            }
            if ($book->getAvailableCopies() <= 0) {
                continue;
            }

            $member = $faker->randomElement($activeMembers);
            $borrowedAt = $faker->dateTimeBetween('-30 days', 'now');
            $isOverdue = $faker->boolean(20);
            $dueDate = $isOverdue
                ? $faker->dateTimeBetween('-15 days', '-1 day')
                : (clone $borrowedAt)->modify('+21 days');

            $loan = new Loan();
            $loan->setBook($book)
                 ->setMember($member)
                 ->setBorrowedAt($borrowedAt)
                 ->setDueDate($dueDate)
                 ->setStatus('borrowed');

            $book->setAvailableCopies($book->getAvailableCopies() - 1);
            $manager->persist($loan);
            $activeLoanCount++;
        }
    }

    private function generateBookTitle(\Faker\Generator $faker): string
    {
        $patterns = [
            fn() => 'Les ' . $faker->words(2, true),
            fn() => 'Le ' . $faker->word(),
            fn() => 'La ' . $faker->word(),
            fn() => 'L\'histoire de ' . $faker->firstName(),
            fn() => ucfirst($faker->words(3, true)),
            fn() => $faker->firstName() . ' et ' . $faker->firstName(),
            fn() => 'Au ' . $faker->word() . ' de ' . $faker->word(),
        ];

        return ($faker->randomElement($patterns))();
    }
}
