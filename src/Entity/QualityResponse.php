<?php

namespace App\Entity;

use App\Repository\QualityResponseRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: QualityResponseRepository::class)]
class QualityResponse
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'qualityResponses', fetch:'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Call $call = null;

    #[ORM\ManyToOne(inversedBy: 'responses', fetch:'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Quality $quality = null;

    #[ORM\ManyToOne(inversedBy: 'qualityResponses', fetch:'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Channel $channel = null;

    #[ORM\ManyToOne(inversedBy: 'qualityResponses', fetch:'EAGER')]
    private ?User $consultant = null;

    #[ORM\Column]
    private ?int $value = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getQuality(): ?Quality
    {
        return $this->quality;
    }

    public function setQuality(?Quality $quality): static
    {
        $this->quality = $quality;

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

    public function getConsultant(): ?User
    {
        return $this->consultant;
    }

    public function setConsultant(?User $consultant): static
    {
        $this->consultant = $consultant;

        return $this;
    }

    public function getValue(): ?int
    {
        return $this->value;
    }

    public function setValue(int $value): static
    {
        $this->value = $value;

        return $this;
    }
}
