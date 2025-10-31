<?php

namespace App\Repository;

use App\Entity\Call;
use App\Entity\Channel;
use App\Entity\Quality;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Channel>
 */
class ChannelRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Channel::class);
    }

    public function getMany() {
        $qb = $this->createQueryBuilder('c');

        $qb->orderBy('c.id', 'desc');

        return $qb;
    }

    public function getCounts()
    {
        $entityManager = $this->getEntityManager();

        $active = $entityManager->createQueryBuilder()
            ->from(Call::class, "c")
            ->select(['COUNT(DISTINCT IDENTITY(c.channel)) as count'])
            ->where("c.closedAt IS NULL")
            ->getQuery()
            ->getSingleColumnResult()[0];


        $total = $this->count();

        return [
            "total" => $total,
            "active" => $active,
            "empty" => $total - $active,
        ];
    }

    public function getUserChannelsQuery(User $user)
    {
        $qb = $this->getMany();

        $qb->join('c.users', 'u');

        $qb->where("u.id = :uid");

        $qb->setParameter("uid", $user->getId());

        return $qb;
    }

    public function getQualityChannelsQuery(Quality $quality)
    {
        $qb = $this->getMany();

        $qb->join('c.qualities', 'q');

        $qb->where("q.id = :qid");

        $qb->setParameter("qid", $quality);

        return $qb;
    }
}
