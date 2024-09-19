<?php

namespace App\Repository;

use App\Entity\TeamMatch;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<TeamMatch>
 *
 * @method TeamMatch|null find($id, $lockMode = null, $lockVersion = null)
 * @method TeamMatch|null findOneBy(array $criteria, array $orderBy = null)
 * @method TeamMatch[]    findAll()
 * @method TeamMatch[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class TeamMatchRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TeamMatch::class);
    }

    public function add(TeamMatch $entity): void
    {
        $this->getEntityManager()->persist($entity);
    }

    public function clear(): void
    {
        $this->createQueryBuilder('m')->delete()->getQuery()->execute();
    }
}
