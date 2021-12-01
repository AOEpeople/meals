<?php

namespace App\Mealz\UserBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Role entity.
 *
 * @ORM\Entity
 * @ORM\Table(name="role")
 * @ORM\Entity(repositoryClass="RoleRepository")
 */
class Role
{
    /**
     * Constants for default roles.
     */
    public const ROLE_KITCHEN_STAFF = 'ROLE_KITCHEN_STAFF';
    public const ROLE_USER = 'ROLE_USER';
    public const ROLE_GUEST = 'ROLE_GUEST';
    public const ROLE_FINANCE = 'ROLE_FINANCE';

    /**
     * Role ID.
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private int $id = 0;

    /**
     * Role name.
     *
     * @ORM\Column(type="string")
     * @Assert\NotBlank
     */
    private string $title = '';

    /**
     * Role string identifier.
     *
     * @ORM\Column(type="string", unique=true)
     * @Assert\NotBlank
     */
    private string $sid = '';

    /**
     * @ORM\ManyToMany(targetEntity="Profile", mappedBy="roles")
     *
     * @var Collection<int, Profile>|null
     */
    private ?Collection $profiles = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getSid(): string
    {
        return $this->sid;
    }

    public function setSid(string $sid): self
    {
        $this->sid = $sid;

        return $this;
    }

    public function getProfiles(): Collection
    {
        return $this->profiles ?? new ArrayCollection();
    }

    public function setProfiles(Collection $profiles): self
    {
        $this->profiles = $profiles;

        return $this;
    }
}
