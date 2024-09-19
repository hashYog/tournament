<?php

namespace Functional\Controller\Api;

use App\Entity\Tournament;
use App\Entity\Team;
use App\Entity\TeamMatch;
use App\Enum\Division;
use App\Enum\Stage;
use App\Repository\TeamMatchRepository;
use App\Repository\TournamentRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TournamentControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $entityManager;
    private TournamentRepository $tournamentRepository;
    private TeamMatchRepository $teamMatchRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->getContainer()->get(EntityManagerInterface::class);
        $this->tournamentRepository = $this->getContainer()->get(TournamentRepository::class);
        $this->teamMatchRepository = $this->getContainer()->get(TeamMatchRepository::class);
        $this->createTournamentAndTeams();
    }

    private function createTournamentAndTeams(): void
    {
        $tournament = new Tournament('Test Tournament');
        $this->entityManager->persist($tournament);

        // Create 8 teams for Division A
        for ($i = 1; $i <= 8; $i++) {
            $team = new Team("Team A$i", Division::DIVISION_A);
            $this->entityManager->persist($team);
        }

        // Create 8 teams for Division B
        for ($i = 1; $i <= 8; $i++) {
            $team = new Team("Team B$i", Division::DIVISION_B);
            $this->entityManager->persist($team);
        }

        $this->entityManager->flush();
    }

    public function testGetTournamentData(): void
    {
        $this->client->request('GET', '/api/tournament');

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('stage', $responseData);
        $this->assertArrayHasKey('divisions', $responseData);
        $this->assertArrayHasKey('playoffMatches', $responseData);
        $this->assertCount(8, $responseData['divisions'][0]['teams']);
        $this->assertCount(8, $responseData['divisions'][1]['teams']);
    }

    public function testGenerateResults(): void
    {
        $tournament = $this->tournamentRepository->get();
        $tournament->updateStage(Stage::BEFORE);
        $this->entityManager->flush();

        $this->client->request('POST', '/api/tournament/generate');
        $tournament = $this->tournamentRepository->get();

        $this->assertResponseIsSuccessful();

        $this->assertEquals(Stage::GROUP, $tournament->getStage());

        $matches = $this->teamMatchRepository->findAll();

        $this->assertCount(112, $matches);
    }

    public function testGetStage(): void
    {
        $this->client->request('GET', '/api/stage');

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $tournament = $this->tournamentRepository->get();
        $this->assertEquals($tournament->getStage()->value, $responseData);
    }

    public function testResetTournament(): void
    {
        $this->client->request('POST', '/api/tournament/reset');

        $this->assertResponseIsSuccessful();
        $responseData = json_decode($this->client->getResponse()->getContent(), true);

        $this->assertEquals(Response::HTTP_OK, $responseData[1]);

        $tournament = $this->tournamentRepository->get();
        $this->assertEquals(Stage::BEFORE, $tournament->getStage());

        $matches = $this->entityManager->getRepository(TeamMatch::class)->findAll();
        $this->assertEmpty($matches);

        $teams = $this->entityManager->getRepository(Team::class)->findAll();
        foreach ($teams as $team) {
            $this->assertEquals(0, $team->getPoints());
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->entityManager->createQuery('DELETE FROM App\Entity\TeamMatch')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Team')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Tournament')->execute();

        $this->entityManager->close();
    }
}
