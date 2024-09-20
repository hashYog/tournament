<?php

declare(strict_types=1);

namespace App\Service\MatchesGenerator;


use App\Entity\Team;
use App\Entity\Tournament;
use App\Enum\Stage;

interface GenerateStrategyInterface
{
    public function getStartStage(): Stage;

    public function generate(Tournament $tournament): void;
}
