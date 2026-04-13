<?php

namespace App\Controller;

use App\Entity\Member;
use App\Form\MemberType;
use App\Repository\LoanRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/members', name: 'app_member_')]
class MemberController extends AbstractController
{
    #[Route('', name: 'index')]
    public function index(): Response
    {
        return $this->render('member/index.html.twig');
    }

    #[Route('/new', name: 'new')]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $member = new Member();
        $member->setMembershipExpiry((new \DateTime())->modify('+1 year'));
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($member);
            $em->flush();

            $this->addFlash('success', 'Adhérent créé avec succès.');
            return $this->redirectToRoute('app_member_show', ['id' => $member->getId()]);
        }

        return $this->render('member/new.html.twig', ['form' => $form]);
    }

    #[Route('/{id}', name: 'show', requirements: ['id' => '\d+'])]
    public function show(Member $member, LoanRepository $loanRepository): Response
    {
        return $this->render('member/show.html.twig', [
            'member' => $member,
            'active_loans' => $loanRepository->findActiveByMember($member->getId()),
        ]);
    }

    #[Route('/{id}/edit', name: 'edit', requirements: ['id' => '\d+'])]
    public function edit(Member $member, Request $request, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(MemberType::class, $member);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();

            $this->addFlash('success', 'Adhérent modifié avec succès.');
            return $this->redirectToRoute('app_member_show', ['id' => $member->getId()]);
        }

        return $this->render('member/edit.html.twig', ['form' => $form, 'member' => $member]);
    }

    #[Route('/{id}/delete', name: 'delete', requirements: ['id' => '\d+'], methods: ['POST'])]
    public function delete(Member $member, Request $request, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete_member_' . $member->getId(), $request->request->get('_token'))) {
            if ($member->getActiveLoans()->count() > 0) {
                $this->addFlash('danger', 'Impossible de supprimer un adhérent avec des emprunts en cours.');
                return $this->redirectToRoute('app_member_show', ['id' => $member->getId()]);
            }
            $em->remove($member);
            $em->flush();
            $this->addFlash('success', 'Adhérent supprimé.');
        }

        return $this->redirectToRoute('app_member_index');
    }
}
