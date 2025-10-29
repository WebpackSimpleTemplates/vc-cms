<?php

namespace App\Repository;

use App\Entity\Call;
use App\Entity\Channel;
use App\Entity\Quality;
use App\Entity\QualityResponse;
use App\Entity\User;
use App\Payload\ReportFilterPayload;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Call>
 */
class CallReportsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Call::class);
    }

    public function mapQueryCalls(QueryBuilder $qb, ReportFilterPayload $filter, string $alias = "c")
    {
        $qb->andWhere($alias.".closedAt IS NOT NULL");

        if ($filter->from()) {
            $qb
                ->andWhere($alias.".waitStart >= :from")
                ->setParameter("from", $filter->from()."T00:00:00.000Z")
            ;
        }

        if ($filter->to()) {
            $qb
                ->andWhere($alias.".waitStart <= :to")
                ->setParameter("to", $filter->to()."T23:59:59.999Z")
            ;
        }
    }

    function fInter(?string $val)
    {
        if (!$val) {
            return "00:00:00";
        }

        return explode(".", explode(",", $val)[0])[0];
    }

    function fNum(null|int|string $val)
    {
        if (!$val) {
            return 0;
        }

        return $val;
    }

    public function getClosed(ReportFilterPayload $filter) {
        $qb = $this->createQueryBuilder('c');

        $qb->where("1 = 1");

        $this->mapQueryCalls($qb, $filter, 'c');

        return $qb;
    }

    public function getAccepted(ReportFilterPayload $filter)
    {
        $qb = $this->getClosed($filter);

        $qb->andWhere("c.acceptedAt IS NOT NULL");

        return $qb;
    }

    public function getRejected(ReportFilterPayload $filter)
    {
        $qb = $this->getClosed($filter);

        $qb->andWhere("c.acceptedAt IS NULL");

        return $qb;
    }

    public function getAcceptedTotals(ReportFilterPayload $filter)
    {
        $qb = $this->getAccepted($filter);

        $qb->select([
            "COUNT(c) AS total",
            "AVG(c.closedAt - c.waitStart) AS avg",
            "AVG(c.acceptedAt - c.waitStart) AS avgWait",
            "MAX(c.acceptedAt - c.waitStart) AS maxWait",
            "AVG(c.closedAt - c.acceptedAt) AS avgServe",
            "MAX(c.closedAt - c.acceptedAt) AS maxServe",
        ]);

        $result = $qb->getQuery()->getScalarResult()[0];

        return [
            "total" => $this->fNum($result['total']),
            "avg" => $this->fInter($result['avg']),
            "avgWait" => $this->fInter($result['avgWait']),
            "maxWait" => $this->fInter($result['maxWait']),
            "avgServe" => $this->fInter($result['avgServe']),
            "maxServe" => $this->fInter($result['maxServe']),
        ];
    }

    public function getRejectedTotals(ReportFilterPayload $filter)
    {
        $qb = $this->getRejected($filter);

        $qb->select([
            "COUNT(c) AS total",
            "AVG(c.closedAt - c.waitStart) AS avg",
            "MAX(c.closedAt - c.waitStart) AS max",
            "MIN(c.closedAt - c.waitStart) AS min",
        ]);

        $result = $qb->getQuery()->getScalarResult()[0];

        return [
            "total" => $this->fNum($result['total']),
            "avg" => $this->fInter($result['avg']),
            "max" => $this->fInter($result['max']),
            "min" => $this->fInter($result['min']),
        ];
    }

    public function getHours(QueryBuilder $qb)
    {
        $qb
            ->select([
                "c.hour AS hour",
                "COUNT(c) AS total",
            ])
            ->groupBy('c.hour')
        ;

        $result = $qb->getQuery()->getScalarResult();

        $hours = [];

        for ($i=0; $i < 24; $i++) {
            $label = ($i > 9 ? "" : "0").$i.":00";

            $setted = false;

            foreach ($result as $row) {
                if ($row['hour'] == $i) {
                    $hours[$label] = $row['total'];
                    $setted = true;
                }
            }

            if (!$setted) {
                $hours[$label] = 0;
            }
        }

        return $hours;
    }

    public function getWeekdays(QueryBuilder $qb)
    {
        $qb
            ->select([
                "c.weekday AS weekday",
                "COUNT(c) AS total",
            ])
            ->groupBy('c.weekday')
        ;

        $result = $qb->getQuery()->getScalarResult();

        $weekdays = [];

        foreach (["Пн", "Вт", "Ср", "Чт", "Пт", "Сб", "Вс"] as $i => $day) {
            $setted = false;

            foreach ($result as $row) {
                if ($row['weekday'] == $i + 1) {
                    $weekdays[$day] = $row['total'];
                    $setted = true;
                }
            }

            if (!$setted) {
                $weekdays[$day] = 0;
            }
        }

        return $weekdays;
    }

    public function getchannels(QueryBuilder $qb)
    {
        $qb
            ->select([
                "COUNT(c) AS total",
                "ch.prefix AS prefix",
            ])
            ->join(Channel::class, 'ch', \Doctrine\ORM\Query\Expr\Join::WITH, 'c.channel = ch')
            ->groupBy('ch.prefix')
        ;

        $result = $qb->getQuery()->getScalarResult();

        $data = [];

        $channels = $this->getEntityManager()
            ->createQueryBuilder()
            ->from(Channel::class, "ch")
            ->select([
                "ch.id AS id",
                "ch.prefix AS prefix",
            ])
            ->getQuery()
            ->getScalarResult();


        foreach ($channels as $channel) {
            $setted = false;

            foreach ($result as $row) {
                if ($row['prefix'] == $channel['prefix']) {
                    $data[$channel['prefix']] = $row['total'];
                    $setted = true;
                }
            }

            if (!$setted) {
                $data[$channel['prefix']] = 0;
            }
        }

        return $data;
    }

    public function getConsultants(ReportFilterPayload $filter)
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->from(User::class, "u")
            ->select([
                "u.id AS id",
                "u.displayName AS displayName",
                "u.email AS email",
                "COUNT(c) as calls",
                "AVG(c.closedAt - c.acceptedAt) as avg",
                "MAX(c.closedAt - c.acceptedAt) as max",
                "MIN(c.closedAt - c.acceptedAt) as min",
            ])
            ->join(Call::class, "c", \Doctrine\ORM\Query\Expr\Join::WITH, "c.consultant = u")
            ->where("1 = 1")
            ->groupBy("u.id", "u.displayName", "u.email")
        ;

        $this->mapQueryCalls($qb, $filter);

        return $qb;
    }

    public function getQualities(ReportFilterPayload $filter)
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->from(Quality::class, "q")
            ->leftJoin("q.responses", "qr")
            ->leftJoin("qr.call", "c")
        ;

        $this->mapQueryCalls($qb, $filter);

        return $qb;
    }

    public function getQualitiesResults(ReportFilterPayload $filter)
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->from(QualityResponse::class, "qr")
            ->join("qr.call", "c")
            ->where("1 = 1");

        $this->mapQueryCalls($qb, $filter);

        return $qb;
    }

    public function getRatesForConsultants(array $userIds, ReportFilterPayload $filter)
    {
        $qb = $this->getQualitiesResults($filter)
            ->leftJoin("qr.quality", "q")
            ->andWhere("qr.consultant IN (:userIds)")
            ->andWhere("q.isConsultant = true")
            ->setParameter("userIds", $userIds)
            ->groupBy('qr.consultant')
            ->select([
                'IDENTITY(qr.consultant) as id',
                'AVG(qr.value) as quality'
            ]);

        return $qb->getQuery()->getResult();
    }

    public function getRatesForCalls(array $callIds)
    {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->from(QualityResponse::class, "qr")
            ->where("qr.call IN (:callIds)")
            ->setParameter("callIds", $callIds)
            ->groupBy('qr.call')
            ->select([
                'IDENTITY(qr.call) as id',
                'AVG(qr.value) as quality'
            ]);

        return $qb->getQuery()->getResult();
    }

    public function getChannelsForClosedCalls(ReportFilterPayload $filter) {
        $qb = $this->getEntityManager()
            ->createQueryBuilder()
            ->from(Channel::class, "ch")
            ->join(Call::class, "c", \Doctrine\ORM\Query\Expr\Join::WITH, "c.channel = ch")
            ->orderBy("ch.id", "DESC");

        $this->mapQueryCalls($qb, $filter);

        return $qb;
    }

    public function getRejectedCallsForChannels(ReportFilterPayload $filter, array $channels)
    {
        return $this->getChannelsForClosedCalls($filter)
            ->andWhere("ch IN(:channels)")
            ->setParameter("channels", $channels)
            ->andWhere("c IS NULL OR c.acceptedAt IS NULL")
            ->select([
                "ch.id as channel",
                "COUNT(c) as rejected",
                "AVG(c.closedAt - c.waitStart) as avgWaitRe",
                "MAX(c.closedAt - c.waitStart) as maxWaitRe",
                "MIN(c.closedAt - c.waitStart) as minWaitRe",
            ])
            ->groupBy("ch.id")
            ->getQuery()
            ->getResult()
        ;
    }

    public function getAcceptedCallsForChannels(ReportFilterPayload $filter, array $channels)
    {
        return $this->getChannelsForClosedCalls($filter)
            ->andWhere("ch IN(:channels)")
            ->setParameter("channels", $channels)
            ->andWhere("c IS NULL OR c.acceptedAt IS NOT NULL")
            ->select([
                "ch.id as channel",
                "COUNT(c) as accepted",
                "AVG(c.closedAt - c.acceptedAt) as avgServ",
                "MAX(c.closedAt - c.acceptedAt) as maxServ",
                "MIN(c.closedAt - c.acceptedAt) as minServ",
                "AVG(c.acceptedAt - c.waitStart) as avgWaitAc",
                "MAX(c.acceptedAt - c.waitStart) as maxWaitAc",
                "MIN(c.acceptedAt - c.waitStart) as minWaitAc",
            ])
            ->groupBy("ch.id")
            ->getQuery()
            ->getResult()
        ;
    }
}
