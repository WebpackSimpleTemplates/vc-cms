<?php

namespace App\Entity;

use App\Repository\ConsultantStatusRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ConsultantStatusRepository::class)]
class ConsultantStatus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    #[ORM\Column]
    private ?int $pauseTime = null;

    #[ORM\Column]
    private ?int $waitTime = null;

    #[ORM\Column]
    private ?int $serveTime = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $userLink = null;

    #[ORM\OneToOne(cascade: ['persist', 'remove'])]
    private ?Call $call = null;

    #[ORM\Column]
    private ?\DateTime $lastOnline = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function getPauseTime(): ?int
    {
        return $this->pauseTime;
    }

    public function setPauseTime(int $pauseTime): static
    {
        $this->pauseTime = $pauseTime;

        return $this;
    }

    public function getWaitTime(): ?int
    {
        return $this->waitTime;
    }

    public function setWaitTime(int $waitTime): static
    {
        $this->waitTime = $waitTime;

        return $this;
    }

    public function getServeTime(): ?int
    {
        return $this->serveTime;
    }

    public function setServeTime(int $serveTime): static
    {
        $this->serveTime = $serveTime;

        return $this;
    }

    public function getUserLink(): ?User
    {
        return $this->userLink;
    }

    public function setUserLink(User $userLink): static
    {
        $this->userLink = $userLink;

        return $this;
    }

    public function getCall(): ?Call
    {
        return $this->call;
    }

    public function setCall(?Call $call): static
    {
        $this->call = $call;

        return $this;
    }

    public function getLastOnline(): ?\DateTime
    {
        return $this->lastOnline;
    }

    public function setLastOnline(\DateTime $lastOnline): static
    {
        $this->lastOnline = $lastOnline;

        return $this;
    }
}
