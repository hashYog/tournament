<?php

namespace App\Controller\Api;

use App\Fetcher\TournamentFetcher;
use App\Repository\TournamentRepository;
use App\Service\MatchesGenerator\Generator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api', name: 'api_')]
class TournamentController extends AbstractController
{
    public function __construct(
        private readonly TournamentRepository $tournamentRepository,
        private readonly Generator $generator,
        private readonly EntityManagerInterface $entityManager,
        private readonly TournamentFetcher $tournamentFetcher,
    ) {
    }

    #[Route('/tournament', name: 'tournament_get', methods: ['GET'])]
    public function getTournamentData(): JsonResponse
    {
        $data = $this->tournamentFetcher->fetchTournamentData();
        return $this->json($data);
    }

    #[Route('/tournament/generate', name: 'tournament_generate', methods: ['POST'])]
    public function generateResults(): Response
    {
        $tournament = $this->tournamentRepository->get();
        $this->generator->generate($this->tournamentRepository->get());
        $this->entityManager->flush();

        return new JsonResponse(['stage' => $tournament->getStage()]);
    }

    #[Route('/tournament/reset', name: 'tournament_reset', methods: ['POST'])]
    public function reset(): Response
    {
        $this->generator->reset($this->tournamentRepository->get());
        $this->entityManager->flush();

        return new JsonResponse(['OK', Response::HTTP_OK]);
    }

    #[Route('/stage', name: 'get_stage', methods: ['GET'])]
    public function getStage(): Response
    {
        return new JsonResponse($this->tournamentRepository->get()->getStage()->value);
    }
}
