<?php

declare(strict_types=1);

namespace App\Entity;

use App\Enum\Division;
use App\Enum\Points;
use App\Repository\TeamRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Team
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name;

    #[ORM\Column(type: 'string', enumType: Division::class)]
    private Division $division;

    #[ORM\Column(type: 'integer')]
    private int $points = 0;

    public function __construct(string $name, Division $division)
    {
        $this->division = $division;
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPoints(): int
    {
        return $this->points;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function updatePoints(Points $points): void
    {
        $this->points += $points->value;
    }

    public function resetPoints(): void
    {
        $this->points = 0;
    }
}
