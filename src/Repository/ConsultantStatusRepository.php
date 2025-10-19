<?php

namespace App\Repository;

use App\Entity\ConsultantStatus;
use DateInterval;
use DateTime;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<ConsultantStatus>
 */
class ConsultantStatusRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, ConsultantStatus::class);
    }

    public function getMany() {
        $qb = $this->createQueryBuilder('c');

        $qb->orderBy('c.id', 'desc');

        return $qb;
    }

    public function getCounts()
    {

        $onlineTime = new DateTime()
            ->add(DateInterval::createFromDateString('-15 second'))
            ->format(DateTime::ATOM);

        $qb = $this->createQueryBuilder("cs")
            ->where("cs.lastOnline < :now")
            ->setParameter("now", $onlineTime)
            ->select(["COUNT(cs) as total"]);

        $total = $qb->getQuery()->getSingleColumnResult()[0];
        $serve = $qb->andWhere("cs.call IS NOT NULL")->getQuery()->getSingleColumnResult()[0];

        return [
            'total' => $total,
            'serve' => $serve,
            'wait' => $total - $serve,
        ];
    }
}
