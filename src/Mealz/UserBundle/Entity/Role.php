<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity]
#[ORM\Table(name: 'role')]
class Role
{
    /**
     * Constants for default roles.
     */
    public const string ROLE_KITCHEN_STAFF = 'ROLE_KITCHEN_STAFF';
    public const string ROLE_USER = 'ROLE_USER';
    public const string ROLE_GUEST = 'ROLE_GUEST';
    public const string ROLE_FINANCE = 'ROLE_FINANCE';

    /**
     * Role ID.
     */
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private int $id = 0;

    /**
     * @var Collection<int, Profile>|null
     */
    #[ORM\ManyToMany(targetEntity: 'Profile', mappedBy: 'roles')]
    private ?Collection $profiles = null;

    /**
     * Role name.
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: 'string')]
    private string $title = '';

    /**
     * Role string identifier.
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: 'string', unique: true)]
    private string $sid = '';

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSid(): string
    {
        return $this->sid;
    }

    public function setSid(string $sid): static
    {
        $this->sid = $sid;

        return $this;
    }

    public function getProfiles(): Collection
    {
        return $this->profiles ?? new ArrayCollection();
    }

    public function setProfiles(Collection $profiles): static
    {
        $this->profiles = $profiles;

        return $this;
    }

    public function __toString(): string
    {
        return $this->getTitle();
    }
}
