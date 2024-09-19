<?php

namespace Unit\Service\MatchesGenerator;

use App\Entity\Tournament;
use App\Enum\Stage;
use App\Repository\TeamMatchRepository;
use App\Repository\TeamRepository;
use App\Service\MatchesGenerator\Generator;
use App\Service\MatchesGenerator\GenerateStrategyInterface;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use LogicException;

class GeneratorTest extends TestCase
{
    private EntityManagerInterface $entityManager;
    private TeamMatchRepository $teamMatchRepository;
    private TeamRepository $teamRepository;

    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->teamMatchRepository = $this->createMock(TeamMatchRepository::class);
        $this->teamRepository = $this->createMock(TeamRepository::class);
    }

    public function testGenerateWithMatchingStrategy(): void
    {
        $tournament = $this->createMock(Tournament::class);
        $tournament->expects($this->once())
            ->method('getStage')
            ->willReturn(Stage::GROUP);

        $strategy = $this->createMock(GenerateStrategyInterface::class);
        $strategy->expects($this->once())
            ->method('getStartStage')
            ->willReturn(Stage::GROUP);
        $strategy->expects($this->once())
            ->method('generate')
            ->with($tournament);

        $generator = new Generator(
            [$strategy],
            $this->entityManager,
            $this->teamMatchRepository,
            $this->teamRepository
        );

        $generator->generate($tournament);
    }

    public function testGenerateWithNoMatchingStrategy(): void
    {
        $tournament = $this->createMock(Tournament::class);
        $tournament->expects($this->once())
            ->method('getStage')
            ->willReturn(Stage::BEFORE);

        $strategy = $this->createMock(GenerateStrategyInterface::class);
        $strategy->expects($this->once())
            ->method('getStartStage')
            ->willReturn(Stage::GROUP);

        $generator = new Generator(
            [$strategy],
            $this->entityManager,
            $this->teamMatchRepository,
            $this->teamRepository
        );

        $this->expectException(LogicException::class);
        $this->expectExceptionMessage('Invalid stage.');

        $generator->generate($tournament);
    }
}
