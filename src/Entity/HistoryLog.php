<?php

namespace App\Entity;

use App\Repository\HistoryLogRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: HistoryLogRepository::class)]
class HistoryLog
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?\DateTime $datetime = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $usr = null;

    #[ORM\Column(length: 255)]
    private ?string $action = null;

    #[ORM\Column(length: 255)]
    private ?string $params = null;

    #[ORM\Column]
    private ?bool $isConsultant = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDatetime(): ?\DateTime
    {
        return $this->datetime;
    }

    public function setDatetime(\DateTime $datetime): static
    {
        $this->datetime = $datetime;

        return $this;
    }

    public function getUsr(): ?User
    {
        return $this->usr;
    }

    public function setUsr(?User $usr): static
    {
        $this->usr = $usr;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(string $action): static
    {
        $this->action = $action;

        return $this;
    }

    public function getParams(): ?string
    {
        return $this->params;
    }

    public function setParams(string $params): static
    {
        $this->params = $params;

        return $this;
    }

    public function isConsultant(): ?bool
    {
        return $this->isConsultant;
    }

    public function setIsConsultant(bool $isConsultant): static
    {
        $this->isConsultant = $isConsultant;

        return $this;
    }

    public function labelTime()
    {
        return $this->datetime->format("d.m.Y H:i:s");
    }
}
