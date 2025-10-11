<?php

namespace App\Repository;

use App\Entity\Call;
use App\Entity\Channel;
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

    public function getChannelsTitles(array $ids)
    {
        $results = $this->createQueryBuilder("c")
            ->select(["c.prefix", "c.id"])
            ->where("c.id IN(:ids)")
            ->setParameter("ids", $ids)
            ->getQuery()
            ->getResult()
        ;

        $prefixs = [];

        foreach ($ids as $id) {
            foreach ($results as $raw) {
                if ($raw['id'] == $id) {
                    $prefixs[] = $raw['prefix'];
                    break;
                }
            }
        }

        return $prefixs;
    }
}
