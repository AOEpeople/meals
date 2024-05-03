<?php

namespace App\Mealz\UserBundle\Entity;

use App\Mealz\UserBundle\User\UserInterface as MealzUserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

/**
 * a set of credentials that allow logging in without LDAP.
 *
 * This can be used for development (easier to set up then LDAP) and special roles
 * like login for a special app in the kitchen where you can check people as participated.
 *
 * @ORM\Table(name="login")
 * @ORM\Entity
 */
class Login implements SymfonyUserInterface, MealzUserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Column(name="id", type="string", length=255, nullable=FALSE)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private string $username = '';

    /**
     * @ORM\Column(type="string", length=32)
     */
    protected string $salt = '';

    /**
     * @ORM\Column(type="string", length=128)
     */
    protected string $password = '';

    /**
     * @ORM\OneToOne(targetEntity="Profile")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     */
    protected ?Profile $profile = null;

    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function setProfile(Profile $profile = null): void
    {
        $this->profile = $profile;
    }

    /**
     * @return Profile
     */
    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    public function setPassword(string $password): void
    {
        $this->password = $password;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setSalt(string $salt): void
    {
        $this->salt = $salt;
    }

    public function getSalt(): string
    {
        return $this->salt;
    }

    public function __toString()
    {
        return $this->getUsername();
    }

    /**
     * @return array Serialized form of the Login object.
     */
    public function __serialize(): array
    {
        return [
            'username' => $this->username,
            'password' => $this->password,
        ];
    }

    /**
     * @param array $data Serialized form of the Login object.
     */
    public function __unserialize(array $data): void
    {
            $this->username = $data['username'];
            $this->password = $data['password'];
    }

    /**
     * {@inheritDoc}
     */
    public function getRoles(): array
    {
        return $this->profile->getRoles();
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials(): void
    {
        // nothing to do here
    }

    /**
     * @inheritdoc
     */
    public function getUserIdentifier(): string
    {
        return $this->username;
    }
}
