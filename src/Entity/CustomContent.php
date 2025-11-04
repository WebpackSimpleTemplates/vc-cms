<?php

namespace App\Entity;

use App\Repository\CustomContentRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CustomContentRepository::class)]
class CustomContent
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $theme = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $logo = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $logoDark = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $marquee = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTheme(): ?string
    {
        return $this->theme;
    }

    public function setTheme(string $theme): static
    {
        $this->theme = $theme;

        return $this;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getLogoDark(): ?string
    {
        return $this->logoDark;
    }

    public function setLogoDark(?string $logoDark): static
    {
        $this->logoDark = $logoDark;

        return $this;
    }

    public function getMarquee(): ?string
    {
        return $this->marquee;
    }

    public function setMarquee(?string $marquee): static
    {
        $this->marquee = $marquee;

        return $this;
    }
}
