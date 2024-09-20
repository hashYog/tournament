<?php

declare(strict_types=1);

namespace App\Controller;

use App\Enum\Stage;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/tournament', name: 'tournament')]
class TournamentController extends AbstractController
{
    #[Route('/', name: '_index')]
    public function index(): Response
    {
        return $this->render('tournament/index.html.twig', [
            'stages' => Stage::values(),
        ]);
    }
}
