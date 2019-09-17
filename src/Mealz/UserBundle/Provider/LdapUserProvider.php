<?php

namespace Mealz\UserBundle\Provider;

use Mealz\UserBundle\Service\PostLogin;
use Mealz\UserBundle\User\LdapUser;
use Symfony\Component\Ldap\LdapClientInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\LdapUserProvider as SymfonyLdapUserProvider;
use Symfony\Component\Security\Core\User\UserInterface;

class LdapUserProvider extends SymfonyLdapUserProvider
{

    private $childDefaultRoles;
    private $postLogin;

    /**
     * @param LdapClientInterface $ldap
     * @param string $baseDn
     * @param string $searchDn
     * @param string $searchPassword
     * @param array $defaultRoles
     * @param string $uidKey
     * @param string $filter
     * @param PostLogin $postLogin
     */
    public function __construct(
        LdapClientInterface $ldap,
        $baseDn,
        $searchDn = null,
        $searchPassword = null,
        array $defaultRoles = array(),
        $uidKey = 'sAMAccountName',
        $filter = '({uid_key}={username})',
        PostLogin $postLogin
    ) {
        parent::__construct($ldap, $baseDn, $searchDn, $searchPassword, $defaultRoles, $uidKey, $filter);
        $this->childDefaultRoles = $defaultRoles;
        $this->postLogin = $postLogin;
    }

    public function loadUser($username, $user)
    {
        $roles = $this->childDefaultRoles;

        if (isset($user['memberof']) && array_search(
                'CN=MealsAdmins_User,OU=_Groups,OU=_Accounts,OU=_AOE,DC=aoemedia,DC=lan',
                $user['memberof']
            ) !== false
        ) {
            array_push($roles, 'ROLE_KITCHEN_STAFF');
        }

        $ldapUser = new LdapUser(
            $user['samaccountname'][0],
            $user['displayname'][0],
            $user['givenname'][0],
            $user['sn'][0],
            $roles
        );
        $this->postLogin->assureProfileExists($ldapUser);

        return $ldapUser;
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof LdapUser) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        $ldapUser = new LdapUser(
            $user->getUsername(),
            $user->getDisplayname(),
            $user->getGivenname(),
            $user->getSurname(),
            $this->childDefaultRoles
        );
        $this->postLogin->assureProfileExists($ldapUser);

        return $ldapUser;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === 'Mealz\UserBundle\User\LdapUser';
    }
}
