<?php

namespace Functional\Service\MatchesGenerator;

use App\Entity\Team;
use App\Entity\TeamMatch;
use App\Entity\Tournament;
use App\Enum\Division;
use App\Enum\Points;
use App\Enum\Stage;
use App\Repository\TeamMatchRepository;
use App\Repository\TeamRepository;
use App\Repository\TournamentRepository;
use App\Service\MatchesGenerator\Generator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GeneratorTest extends KernelTestCase
{
    private EntityManagerInterface $entityManager;
    private Generator $generator;
    private TeamRepository $teamRepository;
    private TeamMatchRepository $teamMatchRepository;
    private TournamentRepository $tournamentRepository;

    protected function setUp(): void
    {
        self::bootKernel();

        $container = static::getContainer();
        $this->entityManager = $container->get(EntityManagerInterface::class);
        $this->generator = $container->get(Generator::class);
        $this->teamRepository = $container->get(TeamRepository::class);
        $this->teamMatchRepository = $container->get(TeamMatchRepository::class);
        $this->tournamentRepository = $container->get(TournamentRepository::class);

        $this->setupTestData();
    }

    public function testReset(): void
    {
        // Verify initial state
        $this->assertCount(8, $this->teamRepository->findAll());
        $this->assertCount(4, $this->teamMatchRepository->findAll());
        $tournament = $this->tournamentRepository->findOneBy([]);
        $this->assertEquals(Stage::QUARTER, $tournament->getStage());

        // Perform reset
        $this->generator->reset($tournament);

        // Verify reset state
        $this->assertCount(8, $this->teamRepository->findAll());
        $this->assertCount(0, $this->teamMatchRepository->findAll());
        $this->assertEquals(Stage::BEFORE, $tournament->getStage());

        // Verify team points are reset
        foreach ($this->teamRepository->findAll() as $team) {
            $this->assertEquals(0, $team->getPoints());
        }
    }

    private function setupTestData(): void
    {
        // Create teams
        $teamsData = [
            ['Team A1', Division::DIVISION_A, 3],
            ['Team A2', Division::DIVISION_A, 1],
            ['Team A3', Division::DIVISION_A, 0],
            ['Team A4', Division::DIVISION_A, 3],
            ['Team B1', Division::DIVISION_B, 3],
            ['Team B2', Division::DIVISION_B, 1],
            ['Team B3', Division::DIVISION_B, 3],
            ['Team B4', Division::DIVISION_B, 0],
        ];

        $teams = [];
        foreach ($teamsData as [$name, $division, $points]) {
            $team = new Team($name, $division);
            if ($points) {
                $team->updatePoints(Points::from($points));
            };
            $this->entityManager->persist($team);
            $teams[] = $team;
        }

        // Create matches
        $matchesData = [
            [$teams[0], $teams[4], 2, 1],
            [$teams[1], $teams[5], 0, 2],
            [$teams[2], $teams[6], 1, 1],
            [$teams[3], $teams[7], 3, 0],
        ];

        foreach ($matchesData as [$team1, $team2, $score1, $score2]) {
            $match = new TeamMatch($team1, $team2, Stage::QUARTER, $score1, $score2);
            $this->entityManager->persist($match);
        }

        // Create tournament
        $tournament = new Tournament('test');
        $tournament->updateStage(Stage::QUARTER);
        $this->entityManager->persist($tournament);

        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Clean up the database
        $this->entityManager->createQuery('DELETE FROM App\Entity\TeamMatch')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Team')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Tournament')->execute();
        $this->entityManager->flush();
    }
}
