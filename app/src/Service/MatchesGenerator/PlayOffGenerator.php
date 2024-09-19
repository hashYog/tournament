<?php

namespace App\Service\MatchesGenerator;

use App\Entity\Team;
use App\Entity\TeamMatch;
use App\Entity\Tournament;
use App\Enum\Division;
use App\Enum\Stage;
use App\Repository\TeamMatchRepository;
use App\Repository\TeamRepository;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;

readonly class PlayOffGenerator implements GenerateStrategyInterface
{
    public function __construct(
        private TeamRepository $teamRepository,
        private EntityManagerInterface $entityManager,
        private TeamMatchRepository $teamMatchRepository,
    ) {
    }

    public function generate(Tournament $tournament): void
    {
        $leaders = $this->getLeaders();
        $this->validateLeadersCount($leaders);

        $semiFinalists = $this->generateQuarterFinals($tournament, $leaders);
        $finalists = $this->generateSemiFinals($tournament, $semiFinalists);
        $this->generateFinal($tournament, $finalists);

        $this->entityManager->flush();
    }

    public function getStartStage(): Stage
    {
        return Stage::GROUP;
    }

    /**
     * @return array{a: array<Team>, b: array<Team>}
     */
    private function getLeaders(): array
    {
        return [
            Division::DIVISION_A->value => $this->teamRepository->findLeadersByDivision(Division::DIVISION_A),
            Division::DIVISION_B->value => $this->teamRepository->findLeadersByDivision(Division::DIVISION_B),
        ];
    }

    /**
     * @param array{a: array<Team>, b: array<Team>} $leaders
     */
    private function validateLeadersCount(array $leaders): void
    {
        if (
            count($leaders[Division::DIVISION_A->value]) !== Tournament::COUNT_LEADERS_IN_DIVISION ||
            count($leaders[Division::DIVISION_B->value]) !== Tournament::COUNT_LEADERS_IN_DIVISION
        ) {
            throw new LogicException('Invalid count of leaders.');
        }
    }

    /**
     * @param array{a: array<Team>, b: array<Team>} $leaders
     * @return array<Team>
     */
    private function generateQuarterFinals(Tournament $tournament, array $leaders): array
    {
        $semiFinalists = [];
        $divisionALeaders = $leaders[Division::DIVISION_A->value];
        $divisionBLeaders = $leaders[Division::DIVISION_B->value];

        while ($first = array_shift($divisionALeaders)) {
            $last = array_pop($divisionBLeaders);
            $match = $this->createMatch($first, $last, Stage::QUARTER);
            $semiFinalists[] = $match->getWinner();
            $tournament->updateStage(Stage::QUARTER);
        }

        return $semiFinalists;
    }

    /**
     * @param array<Team> $semiFinalists
     * @return array<Team>
     */
    private function generateSemiFinals(Tournament $tournament, array $semiFinalists): array
    {
        $finalists = [];
        foreach (array_chunk($semiFinalists, count($semiFinalists) / 2) as $pair) {
            $match = $this->createMatch(current($pair), end($pair), Stage::SEMI);
            $finalists[] = $match->getWinner();
            $tournament->updateStage(Stage::SEMI);
        }

        return $finalists;
    }

    /**
     * @param array<Team> $finalists
     */
    private function generateFinal(Tournament $tournament, array $finalists): void
    {
        $this->createMatch(current($finalists), end($finalists), Stage::FINAL);
        $tournament->updateStage(Stage::FINAL);
    }

    private function createMatch(Team $firstTeam, Team $secondTeam, Stage $stage): TeamMatch
    {
        $match = new TeamMatch($firstTeam, $secondTeam, $stage, ...$this->notDrawRandom());
        $this->teamMatchRepository->add($match);
        return $match;
    }

    /** @return array{0: int, 1: int} */
    private function notDrawRandom(): array
    {
        $first = 0;
        $second = 0;

        while ($first === $second) {
            $first = rand(0, 5);
            $second = rand(0, 5);
        }

        return [$first, $second];
    }
}
