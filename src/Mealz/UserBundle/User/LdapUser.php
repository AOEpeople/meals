<?php

namespace Mealz\UserBundle\User;

use Mealz\UserBundle\Entity\Profile;

class LdapUser implements LdapUserInterface
{

    protected $profile;
    protected $username;
    protected $givenname;
    protected $surname;
    protected $displayname;
    protected $email;
    /**
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    protected $dn;
    /**
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    protected $cn;
    protected $roles = array();
    protected $attributes = array();

    public function __construct($username, $displayname, $givenname, $surname, $roles)
    {
        $this->username = $username;
        $this->displayname = $displayname;
        $this->givenname = $givenname;
        $this->surname = $surname;
        $this->roles = $roles;
    }

    public function getDisplayname()
    {
        return $this->displayname;
    }

    public function setDisplayname($displayname)
    {
        $this->displayname = $displayname;
        return $this;
    }

    public function getGivenname()
    {
        return $this->givenname;
    }

    public function setGivenname($givenname)
    {
        $this->givenname = $givenname;
        return $this;
    }

    public function getSurname()
    {
        return $this->surname;
    }

    public function setSurname($surname)
    {
        $this->surname = $surname;
        return $this;
    }

    public function getRoles()
    {
        return $this->roles;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getPassword()
    {
        return null;
    }

    public function getSalt()
    {
        return null;
    }

    public function getDn()
    {
        return $this->dn;
    }

    /**
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function setDn($dn)
    {
        $this->dn = $dn;

        return $this;
    }

    public function getCn()
    {
        return $this->cn;
    }

    /**
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function setCn($cn)
    {
        $this->cn = $cn;

        return $this;
    }

    public function setAttributes(array $attributes)
    {
        $this->attributes = $attributes;

        return $this;
    }

    public function getAttributes()
    {
        return $this->attributes;
    }

    public function getAttribute($name)
    {
        return isset( $this->attributes[$name] ) ? $this->attributes[$name] : null;
    }

    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }

    public function addRole($role)
    {
        $this->roles[] = $role;

        return $this;
    }

    public function eraseCredentials()
    {
        return null;
    }

    public function isEqualTo(\Symfony\Component\Security\Core\User\UserInterface $user)
    {
        if (!$user instanceof LdapUserInterface
            || $user->getUsername() !== $this->username
            || $user->getEmail() !== $this->email
            || count(array_diff($user->getRoles(), $this->roles)) > 0
            || $user->getDn() !== $this->dn
        ) {
            return false;
        }

        return true;
    }

    public function serialize()
    {
        return serialize(array(
            $this->username,
            $this->email,
            $this->roles,
            $this->dn,
        ));
    }

    public function unserialize($serialized)
    {
        list(
            $this->username,
            $this->email,
            $this->roles,
            $this->dn,
            ) = unserialize($serialized);
    }

    /**
     * Return username when converting class to string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->getUserName();
    }

    /**
     * @param Profile|null $profile
     */
    public function setProfile(Profile $profile = null)
    {
        $this->profile = $profile;
    }

    /**
     * @return Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }
}