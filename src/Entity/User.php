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

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[UniqueEntity(fields: ['email'], message: 'There is already an account with this email')]
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
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable:true)]
    private ?string $displayName = null;

    #[ORM\Column(length: 255, nullable:true)]
    private ?string $fullname = null;

    #[ORM\Column(type: Types::TEXT, nullable:true)]
    private ?string $avatar = null;

    /**
     * @var Collection<int, Channel>
     */
    #[ORM\ManyToMany(targetEntity: Channel::class, inversedBy: 'users')]
    private Collection $channels;

    /**
     * @var Collection<int, Call>
     */
    #[ORM\OneToMany(targetEntity: Call::class, mappedBy: 'consultant')]
    private Collection $calls;

    #[ORM\Column(options:['default'=> false])]
    private ?bool $isConsultant = null;

    public function __construct()
    {
        $this->channels = new ArrayCollection();
        $this->calls = new ArrayCollection();
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

    public function getRolesDisplay() {
        $result = [];

        if (in_array("ROLE_READER", $this->roles)) {
            $result[] = "Читатель";
        }

        if (in_array("ROLE_ADMIN", $this->roles)) {
            $result[] = "Администратор";
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
        $this->avatar = $avatar;

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
}
