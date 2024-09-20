<?php

declare(strict_types=1);

namespace App\Service\MatchesGenerator;

use App\Entity\Tournament;
use App\Repository\TeamMatchRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

readonly class Generator
{
    /** @var array<GenerateStrategyInterface> $strategies */
    public function __construct(
        private iterable $strategies,
        private EntityManagerInterface $entityManager,
        private TeamMatchRepository $teamMatchRepository,
        private TeamRepository $teamRepository,
    )
    {
    }

    public function generate(Tournament $tournament): void
    {
        foreach ($this->strategies as $strategy) {
            if ($tournament->getStage() === $strategy->getStartStage()) {
                $strategy->generate($tournament);

                return;
            }
        }

        throw new LogicException('Invalid stage.');
    }

    public function reset(Tournament $tournament): void
    {
        $this->teamMatchRepository->clear();
        $tournament->reset();

        foreach ($this->teamRepository->findAllRows() as $team) {
            $team->resetPoints();
        }

        $this->entityManager->flush();
    }
}
