<?php

namespace App\Controller;

use App\Entity\Loan;
use App\Form\LoanType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/loans', name: 'app_loan_')]
class LoanController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('loan/index.html.twig');
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $loan = new Loan();
        $loan->setDueDate((new \DateTime())->modify('+21 days'));
        $form = $this->createForm(LoanType::class, $loan);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $book = $loan->getBook();

            if ($book->getAvailableCopies() <= 0) {
                $this->addFlash('danger', 'Ce livre n\'est plus disponible.');
                return $this->render('loan/new.html.twig', ['form' => $form]);
            }

            $book->setAvailableCopies($book->getAvailableCopies() - 1);
            $em->persist($loan);
            $em->flush();

            $this->addFlash('success', 'Emprunt créé avec succès.');
            return $this->redirectToRoute('app_loan_show', ['id' => $loan->getId()]);
        }

        return $this->render('loan/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(Loan $loan): Response
    {
        return $this->render('loan/show.html.twig', ['loan' => $loan]);
    }

    #[Route('/{id}/return', name: 'return', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function return(Loan $loan, Request $request, EntityManagerInterface $em): Response
    {
        if (!$this->isCsrfTokenValid('return_loan_' . $loan->getId(), $request->request->get('_token'))) {
            throw $this->createAccessDeniedException();
        }

        if ($loan->getStatus() !== 'borrowed') {
            $this->addFlash('warning', 'Cet emprunt est déjà clôturé.');
            return $this->redirectToRoute('app_loan_show', ['id' => $loan->getId()]);
        }

        $loan->setStatus('returned');
        $loan->setReturnedAt(new \DateTime());
        $loan->getBook()->setAvailableCopies($loan->getBook()->getAvailableCopies() + 1);
        $em->flush();

        $this->addFlash('success', 'Retour enregistré.');
        return $this->redirectToRoute('app_loan_show', ['id' => $loan->getId()]);
    }
}
