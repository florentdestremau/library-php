<?php

namespace App\Controller;

use App\Entity\Book;
use App\Form\BookType;
use App\Repository\LoanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/books', name: 'app_book_')]
class BookController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('book/index.html.twig');
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $book = new Book();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $book->setAvailableCopies($book->getTotalCopies());
            $em->persist($book);
            $em->flush();

            $this->addFlash('success', 'Livre ajouté avec succès.');
            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }

        return $this->render('book/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(Book $book, LoanRepository $loanRepository): Response
    {
        return $this->render('book/show.html.twig', [
            'book' => $book,
            'active_loans' => $loanRepository->findActiveByBook($book->getId()),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(Book $book, Request $request, EntityManagerInterface $em): Response
    {
        $previousTotal = $book->getTotalCopies();
        $form = $this->createForm(BookType::class, $book);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $diff = $book->getTotalCopies() - $previousTotal;
            $book->setAvailableCopies(max(0, $book->getAvailableCopies() + $diff));
            $em->flush();

            $this->addFlash('success', 'Livre modifié avec succès.');
            return $this->redirectToRoute('app_book_show', ['id' => $book->getId()]);
        }

        return $this->render('book/edit.html.twig', ['form' => $form, 'book' => $book]);
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Book $book, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_book_' . $book->getId(), $request->request->get('_token'))) {
            $em->remove($book);
            $em->flush();
            $this->addFlash('success', 'Livre supprimé.');
        }

        return $this->redirectToRoute('app_book_index');
    }
}
