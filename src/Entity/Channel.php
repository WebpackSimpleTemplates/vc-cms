<?php

namespace App\Entity;

use App\Repository\ChannelRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Attribute\Ignore;

#[ORM\Entity(repositoryClass: ChannelRepository::class)]
class Channel
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT, nullable:true)]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    private ?string $prefix = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, mappedBy: 'channels')]
    #[Ignore]
    private Collection $users;

    /**
     * @var Collection<int, Quality>
     */
    #[ORM\ManyToMany(targetEntity: Quality::class, mappedBy: 'channels')]
    #[Ignore]
    private Collection $qualities;

    /**
     * @var Collection<int, QualityResponse>
     */
    #[ORM\OneToMany(targetEntity: QualityResponse::class, mappedBy: 'channel', orphanRemoval: true)]
    #[Ignore]
    private Collection $qualityResponses;

    /**
     * @var Collection<int, Call>
     */
    #[ORM\OneToMany(targetEntity: Call::class, mappedBy: 'channel', orphanRemoval: true)]
    #[Ignore]
    private Collection $calls;

    #[ORM\OneToOne(mappedBy: 'channel', cascade: ['persist'], fetch:'EAGER', orphanRemoval: true)]
    #[Ignore]
    private ?Schedule $schedule = null;

    #[ORM\Column(nullable: true)]
    #[Ignore]
    private ?\DateTime $deletedAt = null;

    #[ORM\ManyToOne]
    #[Ignore]
    private ?User $deletedBy = null;

    /**
     * @var Collection<int, Quiz>
     */
    #[ORM\ManyToMany(targetEntity: Quiz::class, mappedBy: 'channels')]
    #[Ignore]
    private Collection $quizzes;

    public function __construct()
    {
        $this->users = new ArrayCollection();
        $this->qualities = new ArrayCollection();
        $this->qualityResponses = new ArrayCollection();
        $this->calls = new ArrayCollection();
        $this->quizzes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
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

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function addUser(User $user): static
    {
        if (!$this->users->contains($user)) {
            $this->users->add($user);
            $user->addChannel($this);
        }

        return $this;
    }

    public function removeUser(User $user): static
    {
        if ($this->users->removeElement($user)) {
            $user->removeChannel($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Quality>
     */
    public function getQualities(): Collection
    {
        return $this->qualities;
    }

    public function addQuality(Quality $quality): static
    {
        if (!$this->qualities->contains($quality)) {
            $this->qualities->add($quality);
        }

        return $this;
    }

    public function removeQuality(Quality $quality): static
    {
        $this->qualities->removeElement($quality);

        return $this;
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
            $qualityResponse->setChannel($this);
        }

        return $this;
    }

    public function removeQualityResponse(QualityResponse $qualityResponse): static
    {
        if ($this->qualityResponses->removeElement($qualityResponse)) {
            // set the owning side to null (unless already changed)
            if ($qualityResponse->getChannel() === $this) {
                $qualityResponse->setChannel(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Call>
     */
    public function getCalls(): Collection
    {
        return $this->calls;
    }

    public function addCall(Call $call): static
    {
        if (!$this->calls->contains($call)) {
            $this->calls->add($call);
            $call->setChannel($this);
        }

        return $this;
    }

    public function removeCall(Call $call): static
    {
        if ($this->calls->removeElement($call)) {
            // set the owning side to null (unless already changed)
            if ($call->getChannel() === $this) {
                $call->setChannel(null);
            }
        }

        return $this;
    }

    public function getSchedule(): ?Schedule
    {
        return $this->schedule;
    }

    public function setSchedule(?Schedule $schedule): static
    {
        // unset the owning side of the relation if necessary
        if ($schedule === null && $this->schedule !== null) {
            $this->schedule->setChannel(null);
        }

        // set the owning side of the relation if necessary
        if ($schedule !== null && $schedule->getChannel() !== $this) {
            $schedule->setChannel($this);
        }

        $this->schedule = $schedule;

        return $this;
    }

    public function getDeletedAt(): ?\DateTime
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTime $deletedAt): static
    {
        $this->deletedAt = $deletedAt;

        return $this;
    }

    public function getDeletedBy(): ?User
    {
        return $this->deletedBy;
    }

    public function setDeletedBy(?User $deletedBy): static
    {
        $this->deletedBy = $deletedBy;

        return $this;
    }

    /**
     * @return Collection<int, Quiz>
     */
    public function getQuizzes(): Collection
    {
        return $this->quizzes;
    }

    public function addQuiz(Quiz $quiz): static
    {
        if (!$this->quizzes->contains($quiz)) {
            $this->quizzes->add($quiz);
            $quiz->addChannel($this);
        }

        return $this;
    }

    public function removeQuiz(Quiz $quiz): static
    {
        if ($this->quizzes->removeElement($quiz)) {
            $quiz->removeChannel($this);
        }

        return $this;
    }
}
