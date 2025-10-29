<?php

namespace App\Repository;

use App\Entity\HistoryLog;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<HistoryLog>
 */
class HistoryLogRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, HistoryLog::class);
    }

    public function getActions()
    {
        $qb = $this->createQueryBuilder("l");

        $qb
            ->select([
                "DISTINCT l.action as action"
            ])
            ->orderBy('l.action', 'ASC');

        return $qb->getQuery()->getResult();
    }

    public function getMany(
        ?string $start,
        ?string $end,
        ?string $user,
        ?string $role,
        ?string $action,
    )
    {
        $qb = $this->createQueryBuilder("l");
        $qb->where("1 = 1");

        if ($start) {
            $qb
                ->andWhere("l.datetime >= :start")
                ->setParameter("start", $start)
            ;
        }

        if ($end) {
            $qb
                ->andWhere("l.datetime <= :end")
                ->setParameter("end", $end)
            ;
        }

        if ($action) {
            $qb
                ->andWhere("l.action = :action")
                ->setParameter("action", $action)
            ;
        }

        if ($user) {
            $qb
                ->andWhere("l.usr = :user")
                ->setParameter("user", $user)
            ;
        }

        if ($role) {
            $qb
                ->andWhere("l.isConsultant = ".($role === "ADMIN" ? "false" : "true"))
            ;
        }

        $qb->orderBy("l.datetime", "DESC");

        return $qb;
    }
}
