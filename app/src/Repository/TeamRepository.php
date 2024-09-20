<?php

declare(strict_types=1);

namespace App\Repository;

use App\Entity\Team;
use App\Entity\Tournament;
use App\Enum\Division;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Generator;

/**
 * @method Team|null find($id, $lockMode = null, $lockVersion = null)
 * @method Team|null findOneBy(array $criteria, array $orderBy = null)
 * @method Team[]    findAll()
 * @method Team[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamRepository extends ServiceEntityRepository
{
    private const CHUNK_SIZE = 10;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Team::class);
    }

    /** @return array<Team> */
    public function findByDivision(Division $division): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.division = :division')
            ->setParameter('division', $division)
            ->getQuery()
            ->getResult();
    }

    /**
     * @param array<int> $ids
     *
     * @return Generator<Team>
     */
    public function findAllRows(): Generator
    {
        $ids = $this->createQueryBuilder('t')->select('t.id')->getQuery()->getResult();

        foreach (array_chunk($ids, self::CHUNK_SIZE) as $chunk) {
            $teams = $this->createQueryBuilder('t')
                ->andWhere('t.id IN (:ids)')
                ->setParameter('ids', $chunk)
                ->getQuery()
                ->getResult();

            foreach ($teams as $team) {
                yield $team;
            }

            $this->getEntityManager()->flush();
            $this->getEntityManager()->clear();
        }
    }

    /** @return array<Team> */
    public function findLeadersByDivision(Division $division): array
    {
        return $this->createQueryBuilder('t')
            ->andWhere('t.division = :division')
            ->setParameter('division', $division)
            ->orderBy('t.points', 'desc')
            ->setMaxResults(Tournament::COUNT_LEADERS_IN_DIVISION)
            ->getQuery()
            ->getResult();
    }
}
