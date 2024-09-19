<?php

namespace App\Entity;

use App\Enum\Stage;
use App\Repository\TeamRepository;
use Doctrine\ORM\Mapping as ORM;
use LogicException;

#[ORM\Entity(repositoryClass: TeamRepository::class)]
class Tournament
{
    public const COUNT_LEADERS_IN_DIVISION = 4;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $name;

    #[ORM\Column(type: 'string', enumType: Stage::class, options: ['default' => Stage::BEFORE->value])]
    private Stage $stage = Stage::BEFORE;

    public function __construct(string $name)
    {
        $this->name = $name;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStage(): Stage
    {
        return $this->stage;
    }

    public function updateStage(Stage $stage): void
    {
        if (Stage::getRate($stage) <= $this->stage) {
            throw new LogicException('Cannot set lower stage.');
        }

        $this->stage = $stage;
    }

    public function reset(): void
    {
        $this->stage = Stage::BEFORE;
    }
}
