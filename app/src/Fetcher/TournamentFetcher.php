<?php

declare(strict_types=1);

namespace App\Fetcher;

use App\Enum\Stage;
use Doctrine\DBAL\Connection;

readonly class TournamentFetcher
{
    public function __construct(private Connection $connection)
    {
    }

    // can be decomposited to other fetcher for async calls
    public function fetchTournamentData(): array
    {
        $stage = $this->getCurrentStage();
        $divisions = $this->getDivisions();

        $data = [
            'stage' => $stage,
            'divisions' => [],
        ];

        foreach ($divisions as $division) {
            $teams = $this->getTeams($division);
            $matches = [];

            if ($stage !== Stage::BEFORE) {
                $matches = $this->getMatches($division);
            }

            $data['divisions'][] = [
                'name' => $division,
                'teams' => $teams,
                'matches' => $matches,
            ];
        }

        if ($stage !== Stage::BEFORE && $stage !== Stage::GROUP) {
            $data['playoffMatches'] = $this->getPlayoffMatches();
        }

        return $data;
    }

    public function getStages(): array
    {
        return $this->connection->fetchFirstColumn("SELECT DISTINCT stage FROM tournament_stages ORDER BY id");
    }

    private function getCurrentStage(): string
    {
        return $this->connection->fetchOne("SELECT stage FROM tournament LIMIT 1");
    }

    private function getDivisions(): array
    {
        return $this->connection->fetchFirstColumn("SELECT DISTINCT division FROM team ORDER BY division");
    }

    private function getTeams(string $division): array
    {
        $sql = "SELECT id, name, points FROM team WHERE division = :division ORDER BY points DESC, name";
        return $this->connection->fetchAllAssociative($sql, ['division' => $division]);
    }

    private function getMatches(string $division): array
    {
        $sql = "SELECT 
                    m.id,
                    t1.id AS firstTeamId,
                    t2.id AS secondTeamId,
                    t1.name AS firstTeamName,
                    t2.name AS secondTeamName,
                    m.first_score AS firstScore,
                    m.second_score AS secondScore
                FROM `match` m
                JOIN team t1 ON m.first_team_id = t1.id
                JOIN team t2 ON m.second_team_id = t2.id
                WHERE t1.division = :division AND t2.division = :division
                AND m.stage = 'group'
                ORDER BY m.id";

        return $this->connection->fetchAllAssociative($sql, ['division' => $division]);
    }

    private function getPlayoffMatches(): array
    {
        $sql = "SELECT 
                    m.id,
                    t1.id AS firstTeamId,
                    t2.id AS secondTeamId,
                    t1.name AS firstTeamName,
                    t2.name AS secondTeamName,
                    m.first_score AS firstScore,
                    m.second_score AS secondScore,
                    m.stage
                FROM `match` m
                JOIN team t1 ON m.first_team_id = t1.id
                JOIN team t2 ON m.second_team_id = t2.id
                WHERE m.stage != 'group'
                ORDER BY m.stage, m.id";

        return $this->connection->fetchAllAssociative($sql);
    }
}
