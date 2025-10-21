<?php

namespace App\Entity;

use App\Repository\CallRepository;
use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(repositoryClass: CallRepository::class)]
class Call
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $prefix = null;

    #[ORM\Column]
    private ?int $num = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column]
    private ?\DateTime $waitStart = null;

    #[ORM\ManyToOne(inversedBy: 'calls')]
    #[Ignore]
    private ?User $consultant = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $acceptedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $closedAt = null;

    #[Ignore]
    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?Channel $channel = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrefix(): ?string
    {
        return $this->prefix;
    }

    public function setPrefix(string $prefix): static
    {
        $this->prefix = $prefix;

        return $this;
    }

    public function getNum(): ?int
    {
        return $this->num;
    }

    public function setNum(int $num): static
    {
        $this->num = $num;

        return $this;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): static
    {
        $this->type = $type;

        return $this;
    }

    public function getWaitStart(): ?\DateTime
    {
        return $this->waitStart;
    }

    public function setWaitStart(\DateTime $waitStart): static
    {
        $this->waitStart = $waitStart;

        return $this;
    }

    public function getConsultant(): ?User
    {
        return $this->consultant;
    }

    public function setConsultant(?User $consultant): static
    {
        $this->consultant = $consultant;

        return $this;
    }

    public function getAcceptedAt(): ?\DateTime
    {
        return $this->acceptedAt;
    }

    public function setAcceptedAt(?\DateTime $acceptedAt): static
    {
        $this->acceptedAt = $acceptedAt;

        return $this;
    }

    public function getClosedAt(): ?\DateTime
    {
        return $this->closedAt;
    }

    public function setClosedAt(?\DateTime $closedAt): static
    {
        $this->closedAt = $closedAt;

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

    public function accept(User $user)
    {
        $this->consultant = $user;
        $this->acceptedAt = new DateTime();
    }

    public function getIntervalWait()
    {
        $endWait = new DateTime();

        if ($this->acceptedAt) {
            $endWait = $this->acceptedAt;
        }

        if ($this->closedAt) {
            $endWait = $this->closedAt;
        }

        $timestamp = $endWait->getTimestamp() - $this->waitStart->getTimestamp();

        return date("H:i:s", $timestamp);
    }

    public function getStatus()
    {
        if ($this->closedAt) {
            return "Закрыт";
        }

        if ($this->acceptedAt) {
            return "Обслуживается";
        }

        return "Ожидает";
    }

    public function getIntervalProcess()
    {
        if (!$this->acceptedAt) {
            return "-";
        }

        $endTime = $this->closedAt ?? new DateTime();

        $timestamp = $endTime->getTimestamp() - $this->acceptedAt->getTimestamp();

        return date("H:i:s", $timestamp);
    }
}
