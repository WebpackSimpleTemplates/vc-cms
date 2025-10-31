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
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<Call>
 */
class CallRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Call::class);
    }

    public function getMany()
    {
        $qb = $this->createQueryBuilder('c');

        $qb->orderBy('c.waitStart', 'ASC');

        return $qb;
    }

    public function getActiveMany()
    {
        $qb = $this->getMany();

        $qb->where("c.closedAt IS NULL");

        return $qb;
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
            "avgWait" => $wait['avg'] ? explode(".", $wait['avg'])[0] : '00:00:00',
            "maxWait" => $wait['max'] ? $wait['max'] : '00:00:00',
            "avgServe" => $serve['avg'] ? explode(".", $serve['avg'])[0] : '00:00:00',
            "maxServe" => $serve['max'] ? $serve['max'] : '00:00:00',
        ];
    }

    public function getActiveChannels($serve)
    {
        $onlineTime = new DateTime()->format(DateTime::ATOM);

        $result = $this->createQueryBuilder("c")
            ->select([
                "ch.id as id",
                "ch.title as title",
                "ch.prefix as prefix",
                "COUNT(c) as count",
                "AVG(:now - c.waitStart) as avg",
                "MAX(:now - c.waitStart) as max",
            ])
            ->where("c.acceptedAt IS ".($serve ? " NOT" : "")." NULL AND c.closedAt IS NULL")
            ->leftJoin(Channel::class, "ch", Join::WITH, "ch.id = c.channel")
            ->setParameter("now", $onlineTime)
            ->groupBy("ch")
            ->getQuery()
            ->getResult()
        ;

        return $result;
    }

    public function getActiveCallsCount(User|UserInterface $user)
    {
        $qb = $this->createQueryBuilder('c');
        $channels = $user->getChannels()->toArray();

        $qb
            ->select([
                'COUNT(c) as count'
            ])
            ->where("c.acceptedAt IS NULL")
            ->andWhere("c.closedAt IS NULL");

        if (count($channels))
            {
            $qb = $qb
                ->andWhere("c.channel IN(:ids)")
                ->setParameter("ids", $channels)
            ;
        }

        return $qb->getQuery()->getSingleColumnResult()[0];
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

        if (count($channels))
            {
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

        if ($channel)
            {
            $qb = $qb
                ->andWhere("c.channel = :channel")
                ->setParameter("channel", $channel->getId())
            ;
        } else if ($channels)
        {
            $qb = $qb
                ->andWhere("c.channel IN(:ids)")
                ->setParameter("ids", array_map(fn ($ch) => $ch->getId(), $channels))
            ;
        }

        $result = (array) $qb->getQuery()->getResult();

        return count($result) > 0 ? $result[0] : null;
    }

    public function getNextNum(string $prefix)
    {
        $today = DateTime::createFromFormat("Y-m-d H:i:s", date("Y-m-d")." 00:00:00");

        $qb = $this->createQueryBuilder("c");

        $qb
            ->select([
                "MAX(c.num) as num"
            ])
            ->where("c.prefix = :prefix")
            ->andWhere("c.waitStart > :today")
            ->setParameter("prefix", $prefix)
            ->setParameter("today", $today)
        ;


        return ($qb->getQuery()->getSingleColumnResult()[0] ?? 0) + 1;
    }

    public function getAvgWaitTime()
    {
        $datetime = DateTime::createFromFormat("Y-m-d H:i:s", date("Y-m-d")." 00:00:00");

        $datetime->add(DateInterval::createFromDateString("-7 day"));

        $qb = $this->createQueryBuilder('c');

        $qb->where("c.acceptedAt IS NOT NULL");
        // $qb->andWhere("c.channel = :channel");
        $qb->andWhere("c.waitStart > :datetime");
        $qb->setParameter("datetime", $datetime);

        $qb->select(["AVG(c.acceptedAt - c.waitStart) as time"]);

        $interval = ($qb->getQuery()->getSingleColumnResult()[0] ?? 0);

        if ($interval == 0) {
            return null;
        }

        $comps = explode(":", $interval);

        return ((int) $comps[0]) * 60 * 60 + ((int) $comps[1]) * 60 + ((int) $comps[2]);
    }

    public function getDeferCalls(User $user)
    {
        $qb = $this->getActiveMany();

        $qb->andWhere("c.consultant = :user");
        $qb->setParameter("user", $user);

        return $qb->getQuery()->getResult();
    }
}
