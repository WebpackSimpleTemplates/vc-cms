<?php

namespace App\Entity;

use App\Repository\CallRepository;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Ramsey\Uuid\Doctrine\UuidGenerator;
use Ramsey\Uuid\UuidInterface;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(repositoryClass: CallRepository::class)]
class Call
{
    #[ORM\Id]
    #[ORM\Column(type: "uuid", unique: true)]
    #[ORM\GeneratedValue(strategy: "CUSTOM")]
    #[ORM\CustomIdGenerator(class: UuidGenerator::class)]
    protected ?UuidInterface $id = null;

    #[ORM\Column(length: 255)]
    private ?string $prefix = null;

    #[ORM\Column]
    private ?int $num = null;

    #[ORM\Column(length: 255)]
    private ?string $type = null;

    #[ORM\Column]
    private ?\DateTime $waitStart = null;

    #[ORM\ManyToOne(inversedBy: 'calls', fetch:'EAGER')]
    private ?User $consultant = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $acceptedAt = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTime $closedAt = null;

    #[ORM\ManyToOne(fetch:'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Channel $channel = null;

    /**
     * @var Collection<int, Message>
     */
    #[Ignore]
    #[ORM\OneToMany(targetEntity: Message::class, mappedBy: 'call', orphanRemoval: true)]
    private Collection $messages;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $hour = null;

    #[ORM\Column(type: Types::SMALLINT)]
    private ?int $weekday = null;

    /**
     * @var Collection<int, QualityResponse>
     */
    #[ORM\OneToMany(targetEntity: QualityResponse::class, mappedBy: 'call', orphanRemoval: true)]
    private Collection $qualityResponses;

    #[Ignore]
    #[ORM\Column(length: 255, nullable: true)]
    private ?string $ip = null;

    public function __construct()
    {
        $this->messages = new ArrayCollection();
        $this->qualityResponses = new ArrayCollection();
    }

    public function getId(): ?UuidInterface
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

    public function getDuration()
    {
        $endWait = new DateTime();

        if ($this->closedAt) {
            $endWait = $this->closedAt;
        }

        $timestamp = $endWait->getTimestamp() - $this->waitStart->getTimestamp();

        return date("H:i:s", $timestamp);
    }

    public function getIntervalWait()
    {
        $endWait = new DateTime();

        if ($this->closedAt) {
            $endWait = $this->closedAt;
        }

        if ($this->acceptedAt) {
            $endWait = $this->acceptedAt;
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

    /**
     * @return Collection<int, Message>
     */
    public function getMessages(): Collection
    {
        return $this->messages;
    }

    public function addMessage(Message $message): static
    {
        if (!$this->messages->contains($message)) {
            $this->messages->add($message);
            $message->setCall($this);
        }

        return $this;
    }

    public function removeMessage(Message $message): static
    {
        if ($this->messages->removeElement($message)) {
            // set the owning side to null (unless already changed)
            if ($message->getCall() === $this) {
                $message->setCall(null);
            }
        }

        return $this;
    }

    public function getConsultantName()
    {
        if (!$this->consultant) {
            return "-";
        }

        return $this->consultant->getDisplayName();
    }

    public function getHour(): ?int
    {
        return $this->hour;
    }

    public function setHour(int $hour): static
    {
        $this->hour = $hour;

        return $this;
    }

    public function getWeekday(): ?int
    {
        return $this->weekday;
    }

    public function setWeekday(int $weekday): static
    {
        $this->weekday = $weekday;

        if ($this->weekday === 0) {
            $this->weekday = 7;
        }

        return $this;
    }

    public function formatWaitStart(): string
    {
        return $this->waitStart->format("d.m.Y H:i:s");
    }

    public function formatAcceptedAt(): string
    {
        return $this->acceptedAt ? $this->acceptedAt->format("d.m.Y H:i:s") : "-";
    }

    public function formatClosedAt(): string
    {
        return $this->closedAt ? $this->closedAt->format("d.m.Y H:i:s") : "-";
    }

    /**
     * @return Collection<int, QualityResponse>
     */
    public function getQualityResponses(): Collection
    {
        return $this->qualityResponses;
    }

    public function addQualityResponse(QualityResponse $qualityResponse): static
    {
        if (!$this->qualityResponses->contains($qualityResponse)) {
            $this->qualityResponses->add($qualityResponse);
            $qualityResponse->setCall($this);
        }

        return $this;
    }

    public function removeQualityResponse(QualityResponse $qualityResponse): static
    {
        if ($this->qualityResponses->removeElement($qualityResponse)) {
            // set the owning side to null (unless already changed)
            if ($qualityResponse->getCall() === $this) {
                $qualityResponse->setCall(null);
            }
        }

        return $this;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(?string $ip): static
    {
        $this->ip = $ip;

        return $this;
    }
}
