<?php

namespace App\Repository;

use App\Entity\Call;
use App\Entity\Channel;
use App\Entity\User;
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Call>
 */
class CallRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Call::class);
    }

    public function getActiveCounts()
    {
        $total = $this->count(["closedAt" => null]);
        $wait = $this->count(["closedAt" => null, "acceptedAt" => null]);
        $serve = $total - $wait;

        return [
            "total" => $total,
            "serve" => $serve,
            "wait" => $wait,
        ];
    }

    public function getActiveTimes()
    {
        $onlineTime = new DateTime()->format(DateTime::ATOM);

        $wait = $this->createQueryBuilder("c")
            ->select([
                "AVG(:now - c.waitStart) as avg",
                "MAX(:now - c.waitStart) as max",
            ])
            ->setParameter("now", $onlineTime)
            ->where("c.acceptedAt IS NULL AND c.closedAt IS NULL")
            ->getQuery()
            ->getSingleResult()
        ;

        $serve = $this->createQueryBuilder("c")
            ->select([
                "AVG(:now - c.acceptedAt) as avg",
                "MAX(:now - c.acceptedAt) as max",
            ])
            ->setParameter("now", $onlineTime)
            ->where("c.acceptedAt IS NOT NULL AND c.closedAt IS NULL")
            ->getQuery()
            ->getSingleResult()
        ;

        return [
            "avgWait" => $wait['avg'] ? $wait['avg'] : '00:00:00',
            "maxWait" => $wait['max'] ? $wait['max'] : '00:00:00',
            "avgServe" => $serve['avg'] ? $serve['avg'] : '00:00:00',
            "maxServe" => $serve['max'] ? $serve['max'] : '00:00:00',
        ];
    }

    public function getActiveChannels()
    {
        $onlineTime = new DateTime()->format(DateTime::ATOM);

        return $this->createQueryBuilder("c")
            ->select([
                "ch.id as id",
                "ch.title as title",
                "ch.prefix as prefix",
                "COUNT(c) as count",
                "AVG(:now - c.waitStart) as avg",
                "MAX(:now - c.waitStart) as max",
            ])
            ->where("c.acceptedAt IS NULL AND c.closedAt IS NULL")
            ->leftJoin(Channel::class, "ch", Join::WITH, "ch.id = c.channel")
            ->setParameter("now", $onlineTime)
            ->groupBy("ch")
            ->getQuery()
            ->getResult()
        ;
    }

    public function getServeChannels()
    {
        $onlineTime = new DateTime()->format(DateTime::ATOM);

        return $this->createQueryBuilder("c")
            ->select([
                "IDENTITY(c.channel) as id",
                "COUNT(c) as count",
                "AVG(:now - c.acceptedAt) as avg",
                "MAX(:now - c.acceptedAt) as max",
            ])
            ->where("c.acceptedAt IS NOT NULL AND c.closedAt IS NULL")
            ->setParameter("now", $onlineTime)
            ->groupBy("c.channel")
            ->getQuery()
            ->getResult()
        ;
    }

    public function getActiveChannelsForUser(User $user)
    {
        $channels = $user->getChannels()->toArray();
        $onlineTime = new DateTime()->format(DateTime::ATOM);

        $qb = $this->createQueryBuilder("c")
            ->select([
                "ch.id as id",
                "ch.title as title",
                "ch.prefix as prefix",
                "COUNT(c) as count",
                "AVG(:now - c.waitStart) as avg",
                "MAX(:now - c.waitStart) as max",
            ])
            ->leftJoin(Channel::class, "ch", Join::WITH, "ch.id = c.channel")
            ->where("c.acceptedAt IS NULL")
            ->andWhere("c.closedAt IS NULL")
            ->groupBy("ch")
            ->setParameter("now", $onlineTime)
        ;

        if (count($channels)) {
            $qb = $qb
                ->andWhere("ch.id IN(:ids)")
                ->setParameter("ids", array_map(fn ($ch) => $ch->getId(), $channels))
            ;
        }

        return $qb->getQuery()->getResult();
    }

    public function getNextCall(User $user, ?Channel $channel = null)
    {
        $channels = $user->getChannels()->toArray();

        $qb = $this->createQueryBuilder("c")
            ->orderBy("c.waitStart", "asc")
            ->where("c.closedAt IS NULL")
            ->andWhere("c.acceptedAt IS NULL")
        ;

        if ($channel) {
            $qb = $qb
                ->andWhere("c.channel = :channel")
                ->setParameter("channel", $channel->getId())
            ;
        } else if ($channels) {
            $qb = $qb
                ->andWhere("c.channel IN(:ids)")
                ->setParameter("ids", array_map(fn ($ch) => $ch->getId(), $channels))
            ;
        }

        $result = (array) $qb->getQuery()->getResult();

        return count($result) > 0 ? $result[0] : null;
    }
}
