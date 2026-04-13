<?php

namespace App\Controller;

use App\Repository\BookRepository;
use App\Repository\LoanRepository;
use App\Repository\MemberRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    #[Route('/', name: 'app_home')]
    public function index(
        BookRepository $bookRepository,
        MemberRepository $memberRepository,
        LoanRepository $loanRepository,
    ): Response {
        return $this->render('home/index.html.twig', [
            'stats' => [
                'books' => $bookRepository->count([]),
                'available' => $bookRepository->countAvailable(),
                'members' => $memberRepository->countActive(),
                'active_loans' => $loanRepository->countActive(),
                'overdue' => $loanRepository->countOverdue(),
            ],
        ]);
    }
}
