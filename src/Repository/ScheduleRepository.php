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

    public function copyGeneral(): Schedule
    {
        $general = $this->getGeneral();


        $schedule = new Schedule();
        $schedule->setTimes($general->getTimes());

        return $schedule;
    }

    public function getGeneral(): Schedule
    {
        $schedule = $this->findOneBy(["channel" => null]);

        if (!$schedule) {
            $schedule = new Schedule();
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
