<?php

namespace App\Mealz\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Override;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A profile is some kind of user record in the database that does not know anything about logins.
 *
 * The name "profile" was chosen because in Symfony a "User" is someone who is allowed to log in.
 */
#[ORM\Entity]
#[ORM\Table(name: 'profile')]
class Profile implements UserInterface, JsonSerializable
{
    #[ORM\Id, ORM\GeneratedValue(strategy: 'NONE'), ORM\Column(name: 'id', type: 'string', length: 255, nullable: false)]
    private string $id = '';

    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $username = '';

    #[Assert\NotBlank]
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $name = '';

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string $firstName = '';

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $hidden = false;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $company = '';

    /**
     * @var Collection<int, Role>|null
     */
    #[ORM\ManyToMany(targetEntity: 'Role', inversedBy: 'profiles')]
    private ?Collection $roles = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $settlementHash = null;

    public function setId(string $id): void
    {
        $this->id = $id;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function isHidden(): bool
    {
        return $this->hidden;
    }

    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    public function getFullName(): string
    {
        return "$this->name, $this->firstName";
    }

    public function addRole(Role $role): static
    {
        if (null === $this->roles) {
            $this->roles = new ArrayCollection();
        }

        $this->roles->add($role);

        return $this;
    }

    private function roles(): Collection
    {
        if (null === $this->roles) {
            $this->roles = new ArrayCollection();
        }

        return $this->roles;
    }

    /**
     * @return string[]
     */
    #[Override]
    public function getRoles(): array
    {
        $roles = [];

        foreach ($this->roles() as $role) {
            $roles[] = $role->getSid();
        }

        return $roles;
    }

    /**
     * @param Collection<int, Role> $roles
     */
    public function setRoles(Collection $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function isGuest(): bool
    {
        return $this->roles()->exists(
            function ($key, $role) {
                /* @var Role $role */
                return 'ROLE_GUEST' === $role->getSid();
            }
        );
    }

    public function getCompany(): string
    {
        return $this->company ?? '';
    }

    public function setCompany(string $company): void
    {
        $this->company = $company;
    }

    public function getSettlementHash(): ?string
    {
        return $this->settlementHash;
    }

    public function setSettlementHash(?string $settlementHash): void
    {
        $this->settlementHash = $settlementHash;
    }

    public function getProfile(): static
    {
        return $this;
    }

    public function getPassword(): ?string
    {
        return null;
    }

    #[Override]
    public function eraseCredentials(): void
    {
    }

    public function __toString()
    {
        return $this->getUsername();
    }

    /**
     * @return (string|string|string[])[]
     *
     * @psalm-return array{id: string, user: string, fullName: string, roles: array<string>}
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'user' => $this->username,
            'fullName' => $this->getFullName(),
            'roles' => $this->getRoles(),
        ];
    }

    #[Override]
    public function getUserIdentifier(): string
    {
        return $this->id;
    }
}
