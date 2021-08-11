<?php
/**
 * OAuthUser to provide Keycloak Login
 */

namespace App\Mealz\UserBundle\User;

use App\Mealz\UserBundle\Entity\Profile;

/**
 * OAuthUser.
 */
class OAuthUser implements OAuthUserInterface
{
    /**
     * @var Profile
     */
    protected $profile;

    /**
     * @var string
     */
    protected $username = '';

    /**
     * @var Array
     */
    protected $roles = [];

    /**
     * @param string $username
     */
    public function __construct($username)
    {
        $this->username = $username;
    }

    /**
     * {@inheritdoc}
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    /**
     * {@inheritdoc}
     */
    public function addRole($role)
    {
        if (array_search($role, $this->roles) === false) {
            $this->roles[] = $role;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function removeRole($role)
    {
        if (($key = array_search($role, $this->roles)) !== false) {
            unset($this->roles[$key]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPassword()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getSalt()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getUsername()
    {
        return $this->username;
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

    /**
     * {@inheritdoc}
     */
    public function eraseCredentials()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function equals(UserInterface $user)
    {
        return $user->getUsername() === $this->username;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize()
    {
        return serialize(array($this->username));
    }

    /**
     * {@inheritdoc}
     */
    public function unserialize($serialized)
    {
        list($this->username) = unserialize($serialized);
    }

    /**
     * {@inheritdoc}
     */
    public function isEqualTo(\Symfony\Component\Security\Core\User\UserInterface $user)
    {
        if ($user instanceof LdapUserInterface === false || $user->getUsername() !== $this->username) {
            return false;
        }

        return true;
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
}
