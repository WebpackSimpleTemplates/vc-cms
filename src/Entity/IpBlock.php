<?php

namespace App\Entity;

use App\Repository\IpBlockRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: IpBlockRepository::class)]
class IpBlock
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $ip = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $publicReason = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $privateReason = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getIp(): ?string
    {
        return $this->ip;
    }

    public function setIp(string $ip): static
    {
        $this->ip = $ip;

        return $this;
    }

    public function getPublicReason(): ?string
    {
        return $this->publicReason;
    }

    public function setPublicReason(string $publicReason): static
    {
        $this->publicReason = $publicReason;

        return $this;
    }

    public function getPrivateReason(): ?string
    {
        return $this->privateReason;
    }

    public function setPrivateReason(string $privateReason): static
    {
        $this->privateReason = $privateReason;

        return $this;
    }
}
