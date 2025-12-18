<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Serializer\Attribute\Ignore;
use Symfony\Component\Validator\Constraints\Count;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'Пользователь с такой эл. почтой уже существует')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[Ignore]
    #[ORM\Column]
    #[Count(min:1, minMessage:"Требуется указать хотя бы одну роль")]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Ignore]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable:true)]
    private ?string $displayName = null;

    #[ORM\Column(length: 255, nullable:true)]
    private ?string $fullname = null;

    #[ORM\Column(type: Types::TEXT, options:['default'=> "/default-avatar.jpg"])]
    private ?string $avatar = null;

    #[Ignore]
    /**
     * @var Collection<int, Channel>
     */
    #[ORM\ManyToMany(targetEntity: Channel::class, inversedBy: 'users')]
    private Collection $channels;

    #[Ignore]
    /**
     * @var Collection<int, Call>
     */
    #[ORM\OneToMany(targetEntity: Call::class, mappedBy: 'consultant', orphanRemoval:true)]
    private Collection $calls;

    #[ORM\Column(options:['default'=> false])]
    private ?bool $isConsultant = false;

    /**
     * @var Collection<int, Quality>
     */
    #[Ignore]
    #[ORM\ManyToMany(targetEntity: Quality::class, mappedBy: 'consultants')]
    private Collection $qualities;

    /**
     * @var Collection<int, QualityResponse>
     */
    #[Ignore]
    #[ORM\OneToMany(targetEntity: QualityResponse::class, mappedBy: 'consultant', orphanRemoval:true)]
    private Collection $qualityResponses;

    #[ORM\OneToOne(mappedBy: 'userLink', cascade: ['persist', 'remove'], orphanRemoval:true)]
    #[Ignore]
    private ?ConsultantStatus $consultantStatus = null;

    /**
     * @var Collection<int, HistoryLog>
     */
    #[ORM\OneToMany(targetEntity: HistoryLog::class, mappedBy: 'usr', orphanRemoval: true)]
    #[Ignore]
    private Collection $historyLogs;

    #[ORM\Column(nullable: true)]
    #[Ignore]
    private ?\DateTime $deletedAt = null;

    #[ORM\ManyToOne(targetEntity: self::class)]
    #[Ignore]
    private ?self $deletedBy = null;

    /**
     * @var Collection<int, Quiz>
     */
    #[ORM\ManyToMany(targetEntity: Quiz::class, mappedBy: 'consultants')]
    #[Ignore]
    private Collection $quizzes;

    public function __construct()
    {
        $this->channels = new ArrayCollection();
        $this->calls = new ArrayCollection();
        $this->qualities = new ArrayCollection();
        $this->qualityResponses = new ArrayCollection();
        $this->historyLogs = new ArrayCollection();
        $this->quizzes = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    #[Ignore]
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        if ($this->isRoot() && !in_array("ROLE_ROOT", $roles)) {
            $roles[] = "ROLE_ROOT";
        }

        $this->isConsultant = in_array("ROLE_OPERATOR", $roles);
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Ensure the session doesn't contain actual password hashes by CRC32C-hashing them, as supported since Symfony 7.3.
     */
    public function __serialize(): array
    {
        $data = (array) $this;
        $data["\0" . self::class . "\0password"] = hash('crc32c', $this->password);

        return $data;
    }

    #[Ignore]
    public function getRolesDisplay() {
        $result = [];


        if (in_array("ROLE_ROOT", $this->roles)) {
            $result[] = "Суперпользователь";
        }

        if (in_array("ROLE_ADMIN", $this->roles) && !in_array("ROLE_ROOT", $this->roles)) {
            $result[] = "Администратор";
        }

        if (in_array("ROLE_READER", $this->roles) && !in_array("ROLE_ADMIN", $this->roles) && !in_array("ROLE_ROOT", $this->roles)) {
            $result[] = "Читатель";
        }

        if (in_array("ROLE_OPERATOR", $this->roles)) {
            $result[] = "Консультант";
        }

        return join(", ", $result);
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getDisplayName(): ?string
    {
        return $this->displayName;
    }

    public function setDisplayName(?string $displayName): static
    {
        $this->displayName = $displayName;

        return $this;
    }

    public function getFullname(): ?string
    {
        return $this->fullname;
    }

    public function setFullname(?string $fullname): static
    {
        $this->fullname = $fullname;

        return $this;
    }

    public function getAvatar(): ?string
    {
        return $this->avatar;
    }

    public function setAvatar(?string $avatar): static
    {
        if ($avatar) {
            $this->avatar = $avatar;
        }

        return $this;
    }

    /**
     * @return Collection<int, Channel>
     */
    public function getChannels(): Collection
    {
        return $this->channels;
    }

    public function addChannel(Channel $channel): static
    {
        if (!$this->channels->contains($channel)) {
            $this->channels->add($channel);
        }

        return $this;
    }

    public function removeChannel(Channel $channel): static
    {
        $this->channels->removeElement($channel);

        return $this;
    }

    public function isOperator() {
        return in_array('ROLE_OPERATOR', $this->getRoles());
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
            $call->setConsultant($this);
        }

        return $this;
    }

    public function removeCall(Call $call): static
    {
        if ($this->calls->removeElement($call)) {
            // set the owning side to null (unless already changed)
            if ($call->getConsultant() === $this) {
                $call->setConsultant(null);
            }
        }

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

    public function isRoot()
    {
        return in_array("ROLE_ROOT", $this->roles);
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
            $qualityResponse->setConsultant($this);
        }

        return $this;
    }

    public function removeQualityResponse(QualityResponse $qualityResponse): static
    {
        if ($this->qualityResponses->removeElement($qualityResponse)) {
            // set the owning side to null (unless already changed)
            if ($qualityResponse->getConsultant() === $this) {
                $qualityResponse->setConsultant(null);
            }
        }

        return $this;
    }

    #[Ignore]
    public function getTitle()
    {
        return $this->getDisplayName();
    }

    public function getConsultantStatus(): ?ConsultantStatus
    {
        return $this->consultantStatus;
    }

    public function setConsultantStatus(ConsultantStatus $consultantStatus): static
    {
        // set the owning side of the relation if necessary
        if ($consultantStatus->getUserLink() !== $this) {
            $consultantStatus->setUserLink($this);
        }

        $this->consultantStatus = $consultantStatus;

        return $this;
    }

    /**
     * @return Collection<int, HistoryLog>
     */
    public function getHistoryLogs(): Collection
    {
        return $this->historyLogs;
    }

    public function addHistoryLog(HistoryLog $historyLog): static
    {
        if (!$this->historyLogs->contains($historyLog)) {
            $this->historyLogs->add($historyLog);
            $historyLog->setUsr($this);
        }

        return $this;
    }

    public function removeHistoryLog(HistoryLog $historyLog): static
    {
        if ($this->historyLogs->removeElement($historyLog)) {
            // set the owning side to null (unless already changed)
            if ($historyLog->getUsr() === $this) {
                $historyLog->setUsr(null);
            }
        }

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

    public function getDeletedBy(): ?self
    {
        return $this->deletedBy;
    }

    public function setDeletedBy(?self $deletedBy): static
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
            $quiz->addConsultant($this);
        }

        return $this;
    }

    public function removeQuiz(Quiz $quiz): static
    {
        if ($this->quizzes->removeElement($quiz)) {
            $quiz->removeConsultant($this);
        }

        return $this;
    }
}
