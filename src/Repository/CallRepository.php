<?php

namespace App\Repository;

use App\Entity\Call;
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
        $onlineTime = new DateTime()
            ->add(DateInterval::createFromDateString('-15 second'))
            ->format(DateTime::ATOM);

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
        ];
    }
}
