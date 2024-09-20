<?php

declare(strict_types=1);

namespace App\Service\MatchesGenerator;

use App\Entity\Team;
use App\Entity\TeamMatch;
use App\Entity\Tournament;
use App\Enum\Division;
use App\Enum\Stage;
use App\Event\UpdateStageEvent;
use App\Repository\TeamMatchRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

readonly class DivisionGenerator implements GenerateStrategyInterface
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TeamMatchRepository $repository,
        private TeamRepository $teamRepository,
        private EventDispatcherInterface $dispatcher
    ) {
    }

    public function generate(Tournament $tournament): void
    {
        $divisionATeams = $this->teamRepository->findByDivision(Division::DIVISION_A);
        $divisionBTeams = $this->teamRepository->findByDivision(Division::DIVISION_B);

        $this->generateDivision($divisionATeams);
        $this->generateDivision($divisionBTeams);
        $this->entityManager->flush();

        if (count(array_merge($divisionATeams, $divisionBTeams))) {
            $this->dispatcher->dispatch(new UpdateStageEvent(Stage::GROUP));
        }
    }

    /** @param array<Team> $teams */
    private function generateDivision(array $teams): void
    {
        foreach ($teams as $teamOne) {
            foreach ($teams as $teamTwo) {
                if ($teamOne->getId() === $teamTwo->getId()) {
                    continue;
                }

                $match = new TeamMatch($teamOne, $teamTwo, Stage::GROUP, rand(0, 5), rand(0, 5));
                $match->teamPointsCount();
                $this->repository->add($match);
            }
        }
    }

    public function getStartStage(): Stage
    {
        return Stage::BEFORE;
    }
}
