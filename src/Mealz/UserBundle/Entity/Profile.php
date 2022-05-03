<?php

namespace App\Mealz\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A profile is some kind of user record in the database that does not know anything about logins.
 *
 * The name "profile" was chosen because in Symfony a "User" is someone who is allowed to log in.
 *
 * @ORM\Table(name="profile")
 * @ORM\Entity(repositoryClass="App\Mealz\UserBundle\Entity\ProfileRepository")
 */
class Profile implements UserInterface
{
    /**
     * @ORM\Column(name="id", type="string", length=255, nullable=FALSE)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private string $username = '';

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=255, nullable=TRUE)
     */
    private string $name = '';

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=255, nullable=TRUE)
     */
    private string $firstName = '';

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     */
    private bool $hidden = false;

    /**
     * @ORM\Column(type="string", length=255, nullable=TRUE)
     */
    private ?string $company = '';

    /**
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="profiles")
     *
     * @var Collection<int, Role>|null
     */
    private ?Collection $roles = null;

    /**
     * @ORM\Column(type="string", length=255, nullable=TRUE)
     */
    private ?string $settlementHash = null;

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

    public function __toString()
    {
        return $this->getUsername();
    }

    public function addRole(Role $role): self
    {
        if (null === $this->roles) {
            $this->roles = new ArrayCollection();
        }

        $this->roles->add($role);

        return $this;
    }

    public function removeRole(Role $role): void
    {
        if (null !== $this->roles) {
            $this->roles->removeElement($role);
        }
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

    public function getProfile(): self
    {
        return $this;
    }

    public function getPassword(): ?string
    {
        return null;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {
    }
}
