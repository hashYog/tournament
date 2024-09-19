<?php

namespace App\DataFixtures;

use App\Entity\Team;
use App\Entity\Tournament;
use App\Enum\Division;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class TournamentFixture extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $tournament = new Tournament('Tournament');

        $teamNames = [
            'Team A1', 'Team A2', 'Team A3', 'Team A4', 'Team A5', 'Team A6', 'Team A7', 'Team A8',
            'Team B1', 'Team B2', 'Team B3', 'Team B4', 'Team B5', 'Team B6', 'Team B7', 'Team B8',
        ];

        foreach ($teamNames as $index => $name) {
            if ($index < 8) {
                $manager->persist(new Team($name, Division::DIVISION_A));
            } else {
                $manager->persist(new Team($name, Division::DIVISION_B));
            }
        }

        $manager->persist($tournament);
        $manager->flush();
    }
}
