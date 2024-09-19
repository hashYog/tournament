<?php

namespace Unit\Service\MatchesGenerator;

use App\Entity\Team;
use App\Entity\TeamMatch;
use App\Entity\Tournament;
use App\Enum\Division;
use App\Enum\Stage;
use App\Repository\TeamMatchRepository;
use App\Repository\TeamRepository;
use App\Service\MatchesGenerator\PlayOffGenerator;
use Doctrine\ORM\EntityManagerInterface;
use LogicException;
use PHPUnit\Framework\TestCase;

class PlayOffGeneratorTest extends TestCase
{
    private $teamRepositoryMock;
    private $entityManagerMock;
    private $teamMatchRepositoryMock;
    private $playOffGenerator;

    protected function setUp(): void
    {
        $this->teamRepositoryMock = $this->createMock(TeamRepository::class);
        $this->entityManagerMock = $this->createMock(EntityManagerInterface::class);
        $this->teamMatchRepositoryMock = $this->createMock(TeamMatchRepository::class);

        $this->playOffGenerator = new PlayOffGenerator(
            $this->teamRepositoryMock,
            $this->entityManagerMock,
            $this->teamMatchRepositoryMock
        );
    }

    public function testGenerateWithValidLeaders()
    {
        $tournament = new Tournament('test');

        $divisionALeaders = [
            $this->createConfiguredMock(Team::class, ['getId' => 1]),
            $this->createConfiguredMock(Team::class, ['getId' => 2]),
            $this->createConfiguredMock(Team::class, ['getId' => 3]),
            $this->createConfiguredMock(Team::class, ['getId' => 4]),
        ];
        $divisionBLeaders = [
            $this->createConfiguredMock(Team::class, ['getId' => 5]),
            $this->createConfiguredMock(Team::class, ['getId' => 6]),
            $this->createConfiguredMock(Team::class, ['getId' => 7]),
            $this->createConfiguredMock(Team::class, ['getId' => 8]),
        ];

        $this->teamRepositoryMock->expects($this->exactly(2))
            ->method('findLeadersByDivision')
            ->withConsecutive(
                [Division::DIVISION_A],
                [Division::DIVISION_B]
            )
            ->willReturnOnConsecutiveCalls($divisionALeaders, $divisionBLeaders);

        $this->teamMatchRepositoryMock->expects($this->exactly(7))
            ->method('add')
            ->withConsecutive(
                [$this->callback(function (TeamMatch $match) use ($divisionALeaders, $divisionBLeaders) {
                    return in_array($match->getFirstTeam(), $divisionALeaders, true) &&
                        in_array($match->getSecondTeam(), $divisionBLeaders, true) &&
                        $match->getStage() === Stage::QUARTER;
                })],
                [$this->callback(function (TeamMatch $match) use ($divisionALeaders, $divisionBLeaders) {
                    return $match->getStage() === Stage::QUARTER;
                })],
                [$this->callback(function (TeamMatch $match) use ($divisionALeaders, $divisionBLeaders) {
                    return $match->getStage() === Stage::QUARTER;
                })],
                [$this->callback(function (TeamMatch $match) use ($divisionALeaders, $divisionBLeaders) {
                    return $match->getStage() === Stage::QUARTER;
                })],
                [$this->callback(function (TeamMatch $match) use ($divisionALeaders, $divisionBLeaders) {
                    return $match->getStage() === Stage::SEMI;
                })],
                [$this->callback(function (TeamMatch $match) use ($divisionALeaders, $divisionBLeaders) {
                    return $match->getStage() === Stage::SEMI;
                })],
                [$this->callback(function (TeamMatch $match) use ($divisionALeaders, $divisionBLeaders) {
                    return $match->getStage() === Stage::FINAL;
                })],
            );

        $this->entityManagerMock->expects($this->once())->method('flush');

        $this->playOffGenerator->generate($tournament);
    }

    public function testGenerateWithInvalidLeaderCount()
    {
        $tournament = new Tournament('test');

        $divisionALeaders = [
            $this->createMock(Team::class),
            $this->createMock(Team::class),
            $this->createMock(Team::class),
        ];
        $divisionBLeaders = [
            $this->createMock(Team::class),
            $this->createMock(Team::class),
            $this->createMock(Team::class),
            $this->createMock(Team::class),
        ];

        $this->teamRepositoryMock->expects($this->exactly(2))
            ->method('findLeadersByDivision')
            ->withConsecutive(
                [Division::DIVISION_A],
                [Division::DIVISION_B]
            )
            ->willReturnOnConsecutiveCalls($divisionALeaders, $divisionBLeaders);

        $this->teamMatchRepositoryMock->expects($this->never())->method('add');

        $this->entityManagerMock->expects($this->never())->method('flush');
        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Invalid count of leaders.');

        $this->playOffGenerator->generate($tournament);
    }
}
