<?php

namespace App\Entity;

use App\Repository\ScheduleRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ScheduleRepository::class)]
class Schedule
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private array $times = [];

    #[ORM\OneToOne(inversedBy: 'schedule', cascade: ['persist'])]
    private ?Channel $channel = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTimes(): array
    {
        return $this->times;
    }

    public function setTimes(array $times): static
    {
        $this->times = $times;

        return $this;
    }

    public function getChannel(): ?Channel
    {
        return $this->channel;
    }

    public function setChannel(?Channel $channel): static
    {
        $this->channel = $channel;

        return $this;
    }

    public function isActive(): bool
    {
        $dayWeek = ((int) date("w")) - 1;

        if ($dayWeek == -1) {
            $dayWeek = 6;
        }

        $minutes = ((int) date('H')) * 60 + ((int) date('i'));

        foreach ($this->times[$dayWeek] as $range) {
            if ($range[0] >= $minutes && $range[1] <= $minutes) {
                return true;
            }
        }

        return false;
    }
}
