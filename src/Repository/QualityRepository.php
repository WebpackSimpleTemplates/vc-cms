<?php

namespace App\Repository;

use App\Entity\Channel;
use App\Entity\Quality;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Quality>
 */
class QualityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Quality::class);
    }

    public function getMany()
    {
        $qb = $this->createQueryBuilder('q');

        $qb->orderBy('q.id', 'DESC');

        return $qb;
    }

    public function getNotMain()
    {
        $qb = $this->getMany();

        $qb->where("q.isMain = false");

        return $qb;
    }

    public function getChannelQualitiesQuery(Channel $channel)
    {
        $qb = $this->getNotMain();

        $qb->join('q.channels', 'c');

        $qb->andWhere("c.id = :cid");

        $qb->setParameter("cid", $channel->getId());

        return $qb;
    }

    public function getUserQualitiesQuery(User $user)
    {
        $qb = $this->getNotMain();

        $qb->join('q.consultants', 'u');

        $qb->andWhere("u.id = :uid");

        $qb->setParameter("uid", $user->getId());

        return $qb;
    }
}
