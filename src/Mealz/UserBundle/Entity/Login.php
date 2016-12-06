<?php

namespace Mealz\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mealz\UserBundle\User\UserInterface as MealzUserInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

/**
 * a set of credentials that allow logging in without LDAP
 *
 * This can be used for development (easier to set up then LDAP) and special roles
 * like login for a special app in the kitchen where you can check people as participated.
 *
 * @ORM\Table(name="login")
 * @ORM\Entity
 */
class Login implements SymfonyUserInterface, \Serializable, MealzUserInterface
{
    /**
     * @var string
     *
     * @ORM\Column(name="id", type="string", length=255, nullable=FALSE)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="NONE")
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=32)
     * @var string
     */
    protected $salt;

    /**
     * @ORM\Column(type="string", length=64)
     * @var string
     */
    protected $password;

    /**
     * @ORM\OneToOne(targetEntity="Profile")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     * @var Profile
     */
    protected $profile;

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
     * @param \Mealz\UserBundle\Entity\Profile $profile
     */
    public function setProfile(Profile $profile = null)
    {
        $this->profile = $profile;
    }

    /**
     * @return \Mealz\UserBundle\Entity\Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    /**
     * @param string $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param string $salt
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;
    }

    /**
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    public function __toString()
    {
        return $this->getUsername();
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * String representation of object
     * @link http://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize()
    {
        return serialize(
            array(
                $this->username,
                $this->salt,
                $this->password,
            )
        );
    }

    /**
     * (PHP 5 &gt;= 5.1.0)<br/>
     * Constructs the object
     * @link http://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized)
    {
        list (
            $this->username,
            $this->salt,
            $this->password,
            ) = unserialize($serialized);
    }

    /**
     * Returns the roles granted to the user.
     *
     * <code>
     * public function getRoles()
     * {
     *     return array('ROLE_USER');
     * }
     * </code>
     *
     * Alternatively, the roles might be stored on a ``roles`` property,
     * and populated in any number of different ways when the user object
     * is created.
     *
     * @return Role[] The user roles
     */
    public function getRoles()
    {
        $roles = array('ROLE_USER');
        if ($this->getUsername() === 'kochomi') {
            array_push($roles, 'ROLE_KITCHEN_STAFF', 'ROLE_CONFIRMATION');
        }

        return $roles;
    }

    /**
     * Removes sensitive data from the user.
     *
     * This is important if, at any given point, sensitive information like
     * the plain-text password is stored on this object.
     */
    public function eraseCredentials()
    {
        // nothing to do here
    }
}
