<?php

namespace Unit\Service\MatchesGenerator;

use App\Entity\Team;
use App\Entity\Tournament;
use App\Enum\Division;
use App\Enum\Stage;
use App\Event\UpdateStageEvent;
use App\Repository\TeamMatchRepository;
use App\Repository\TeamRepository;
use App\Service\MatchesGenerator\DivisionGenerator;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class DivisionGeneratorTest extends TestCase
{
    private EntityManagerInterface $entityManagerMock;
    private TeamMatchRepository $teamMatchRepositoryMock;
    private TeamRepository $teamRepositoryMock;
    private EventDispatcherInterface $eventDispatcherMock;
    private DivisionGenerator $divisionGenerator;

    protected function setUp(): void
    {
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->teamMatchRepositoryMock = $this->createMock(TeamMatchRepository::class);
        $this->teamRepositoryMock = $this->createMock(TeamRepository::class);
        $this->eventDispatcherMock = $this->createMock(EventDispatcherInterface::class);

        $this->divisionGenerator = new DivisionGenerator(
            $this->entityManagerMock,
            $this->teamMatchRepositoryMock,
            $this->teamRepositoryMock,
            $this->eventDispatcherMock
        );
    }

    public function testGenerateWithTeamsInBothDivisions()
    {
        $tournament = new Tournament('test');

        $divisionATeams = [
            $this->createConfiguredMock(Team::class, ['getId' => 1]),
            $this->createConfiguredMock(Team::class, ['getId' => 2]),
            $this->createConfiguredMock(Team::class, ['getId' => 3]),
            $this->createConfiguredMock(Team::class, ['getId' => 4]),
            $this->createConfiguredMock(Team::class, ['getId' => 5]),
            $this->createConfiguredMock(Team::class, ['getId' => 6]),
            $this->createConfiguredMock(Team::class, ['getId' => 7]),
            $this->createConfiguredMock(Team::class, ['getId' => 8]),
        ];
        $divisionBTeams = [
            $this->createConfiguredMock(Team::class, ['getId' => 9]),
            $this->createConfiguredMock(Team::class, ['getId' => 10]),
            $this->createConfiguredMock(Team::class, ['getId' => 11]),
            $this->createConfiguredMock(Team::class, ['getId' => 12]),
            $this->createConfiguredMock(Team::class, ['getId' => 13]),
            $this->createConfiguredMock(Team::class, ['getId' => 14]),
            $this->createConfiguredMock(Team::class, ['getId' => 15]),
            $this->createConfiguredMock(Team::class, ['getId' => 16]),
        ];

        $this->teamRepositoryMock->expects($this->exactly(2))
            ->method('findByDivision')
            ->withConsecutive(
                [Division::DIVISION_A],
                [Division::DIVISION_B]
            )
            ->willReturnOnConsecutiveCalls($divisionATeams, $divisionBTeams);

        $this->teamMatchRepositoryMock->expects($this->exactly(112))->method('add');

        $this->entityManagerMock->expects($this->once())
            ->method('flush');

        $this->eventDispatcherMock->expects($this->once())
            ->method('dispatch')
            ->with($this->isInstanceOf(UpdateStageEvent::class));

        $this->divisionGenerator->generate($tournament);
    }

    public function testGenerateWithNoTeamsInDivisions()
    {
        $tournament = new Tournament('test');

        $this->teamRepositoryMock->expects($this->exactly(2))
            ->method('findByDivision')
            ->withConsecutive(
                [Division::DIVISION_A],
                [Division::DIVISION_B]
            )
            ->willReturnOnConsecutiveCalls([], []);

        $this->teamMatchRepositoryMock->expects($this->never())
            ->method('add');

        $this->entityManagerMock->expects($this->once())
            ->method('flush');

        $this->eventDispatcherMock->expects($this->never())
            ->method('dispatch');

        $this->divisionGenerator->generate($tournament);
    }

    public function testGetStartStage()
    {
        $this->assertEquals(Stage::BEFORE, $this->divisionGenerator->getStartStage());
    }
}
