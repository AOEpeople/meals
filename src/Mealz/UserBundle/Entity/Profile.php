<?php

namespace Mealz\UserBundle\Entity;

use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUser;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * A profile is some kind of user record in the database that does not know anything about logins.
 *
 * The name "profile" was chosen because in Symfony a "User" is someone who is allowed to log in.
 *
 * @ORM\Table(name="profile")
 * @ORM\Entity(repositoryClass="ProfileRepository")
 */
class Profile extends OAuthUser
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="string", length=255, nullable=FALSE)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    protected $username;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=255, nullable=TRUE)
     * @var string
     */
    protected $name;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=255, nullable=TRUE)
     * @var string
     */
    protected $firstName;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="string", length=255, nullable=TRUE)
     * @var string
     */
    protected $company;

    /**
     * @ORM\OneToMany(targetEntity="Mealz\AccountingBundle\Entity\Transaction", mappedBy="profile")
     * @var ArrayCollection
     */
    protected $transactions;

    /**
     * @ORM\ManyToMany(targetEntity="Role", inversedBy="profiles")
     * @var Collection
     */
    protected $roles;

    /**
     * @ORM\Column(type="string", length=255, nullable=TRUE)
     * @var string
     */
    private $settlementHash;

    /**
     * Profile constructor.
     */
    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    /**
     * @param string $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param string $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return string
     */
    public function getFullName()
    {
        return "$this->name, $this->firstName";
    }

    public function __toString()
    {
        return $this->getUsername();
    }

    /**
     * Add role
     *
     * @param Role $role
     * @return Profile
     */
    public function addRole(Role $role)
    {
        $this->roles->add($role);

        return $this;
    }

    /**
     * Remove role
     *
     * @param Role $role
     */
    public function removeRole(Role $role)
    {
        $this->roles->removeElement($role);
    }

    /**
     * @return array
     */
    public function getRoles()
    {
        return $this->roles->toArray();
    }

    /**
     * @param mixed $roles
     */
    public function setRoles(Collection $roles)
    {
        $this->roles = $roles;
    }

    /**
     * is the User a Guest
     * @return bool
     */
    public function isGuest()
    {
        return $this->roles->exists(
            function ($key, $role) {
                /** @var Role $role */
                return ($role->getSid() === 'ROLE_GUEST');
            }
        );
    }

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param string $company
     */
    public function setCompany($company)
    {
        $this->company = $company;
    }

    /**
     * @return mixed
     */
    public function getSettlementHash()
    {
        return $this->settlementHash;
    }

    /**
     * @param mixed $settlementHash
     */
    public function setSettlementHash($settlementHash)
    {
        $this->settlementHash = $settlementHash;
    }

    /**
     * @return Profile
     */
    public function getProfile()
    {
        return $this;
    }
}
