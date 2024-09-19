<?php

namespace App\Repository;

use App\Entity\Tournament;
use App\Exception\NotFoundException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Tournament[] findAll()
 */
class TournamentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Tournament::class);
    }

    public function get(): Tournament
    {
        if (!$tournament = $this->findAll()) {
            throw new NotFoundException('Tournament not found');
        }

        return current($tournament);
    }
}
