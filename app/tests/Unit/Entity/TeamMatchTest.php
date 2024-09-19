<?php

namespace Unit\Entity;

use App\Entity\Team;
use App\Entity\TeamMatch;
use App\Enum\Division;
use App\Enum\Points;
use App\Enum\Stage;
use PHPUnit\Framework\TestCase;

class TeamMatchTest extends TestCase
{
    public function testGetWinner()
    {
        $team1 = new Team('Team 1', Division::DIVISION_A);
        $team2 = new Team('Team 2', Division::DIVISION_B);

        $match1 = new TeamMatch($team1, $team2, Stage::GROUP, 3, 2);
        $match2 = new TeamMatch($team1, $team2, Stage::GROUP, 2, 3);

        $this->assertEquals($team1, $match1->getWinner());
        $this->assertEquals($team2, $match2->getWinner());
    }

    public function testTeamPointsCount()
    {
        $team1 = new Team('Team 1', Division::DIVISION_A);
        $team2 = new Team('Team 2', Division::DIVISION_B);

        $match1 = new TeamMatch($team1, $team2, Stage::GROUP, 3, 2);
        $match2 = new TeamMatch($team1, $team2, Stage::GROUP, 2, 3);
        $match3 = new TeamMatch($team1, $team2, Stage::GROUP, 2, 2);

        $match1->teamPointsCount();
        $match2->teamPointsCount();
        $match3->teamPointsCount();

        $this->assertEquals(4, $team1->getPoints());
        $this->assertEquals(4, $team2->getPoints());
    }
}
