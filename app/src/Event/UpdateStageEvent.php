<?php

declare(strict_types=1);

namespace App\Event;

use App\Enum\Stage;
use Symfony\Contracts\EventDispatcher\Event;

class UpdateStageEvent extends Event
{
    public function __construct(public Stage $stage)
    {
    }
}
