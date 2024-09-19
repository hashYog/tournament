<?php

namespace App\Event;

use App\Enum\Stage;
use Symfony\Contracts\EventDispatcher\Event;

class UpdateStageEvent extends Event
{
    public function __construct(public Stage $stage)
    {
    }
}
