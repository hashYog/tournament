<?php

namespace App\Entity;

use App\Enum\Points;
use App\Enum\Stage;
use App\Repository\TeamMatchRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamMatchRepository::class)]
#[ORM\Table(name: '`match`')]
class TeamMatch
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Team $firstTeam;

    #[ORM\ManyToOne(targetEntity: Team::class)]
    #[ORM\JoinColumn(nullable: false)]
    private Team $secondTeam;

    #[ORM\Column(type: 'integer')]
    private int $firstScore;

    #[ORM\Column(type: 'integer')]
    private int $secondScore;

    #[ORM\Column(type: 'string', enumType: Stage::class)]
    private Stage $stage;

    public function __construct(
        Team $firstTeam,
        Team $secondTeam,
        Stage $stage,
        int $firstScore,
        int $secondScore,
    )
    {
        $this->stage = $stage;
        $this->firstTeam = $firstTeam;
        $this->secondTeam = $secondTeam;
        $this->firstScore = $firstScore;
        $this->secondScore = $secondScore;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstTeam(): Team
    {
        return $this->firstTeam;
    }

    public function getSecondTeam(): Team
    {
        return $this->secondTeam;
    }

    public function getStage(): Stage
    {
        return $this->stage;
    }

    public function getWinner(): Team
    {
        return $this->firstScore > $this->secondScore ? $this->firstTeam : $this->secondTeam;
    }

    public function teamPointsCount(): void
    {
        match (true) {
            $this->firstScore > $this->secondScore => $this->firstTeam->updatePoints(Points::WIN),
            $this->firstScore < $this->secondScore => $this->secondTeam->updatePoints(Points::WIN),
            default => $this->draw(),
        };
    }

    private function draw(): void
    {
        $this->firstTeam->updatePoints(Points::DRAW);
        $this->secondTeam->updatePoints(Points::DRAW);
    }
}
