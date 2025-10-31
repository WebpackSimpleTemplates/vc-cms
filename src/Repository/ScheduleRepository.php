<?php

namespace App\Repository;

use App\Entity\Schedule;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Schedule>
 */
class ScheduleRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Schedule::class);
    }

    public function getGeneral(): Schedule
    {
        $general = $this->findOneBy(["channel" => null]);

        $schedule = new Schedule();

        if ($general) {
            $schedule->setTimes($general->getTimes());
        } else {
            $schedule->setTimes([
                [[0, 1440]],
                [[0, 1440]],
                [[0, 1440]],
                [[0, 1440]],
                [[0, 1440]],
                [[0, 1440]],
                [[0, 1440]],
            ]);
        }

        return $schedule;
    }
}
