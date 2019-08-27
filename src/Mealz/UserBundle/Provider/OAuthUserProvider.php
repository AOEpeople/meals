<?php

namespace Mealz\UserBundle\Provider;

use Mealz\UserBundle\Entity\Profile;
use Mealz\UserBundle\Entity\Profile\ProfileRepository;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use \Doctrine\Bundle\DoctrineBundle\Registry;
use Mealz\UserBundle\Service\PostLogin;
use Symfony\Bridge\Monolog\Logger;
use Mealz\UserBundle\Provider\LdapUserProvider;
use \HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider as HWIOAuthUserProvider;

/**
 * Class OAuthUserProvider
 */
class OAuthUserProvider extends HWIOAuthUserProvider
{
    /**
     * @var ProfileRepository
     */
    private $profileRepository;

    /**
     * @var PostLogin
     */
    private $postLogin;

    /**
     * @var LdapUserProvider
     */
    private $ldapUserProvider;


    /**
     * OAuthUserProvider constructor.
     *
     * @param Registry $doctrineRegistry
     * @param PostLogin $postLogin
     */
    public function __construct(Registry $doctrineRegistry = null, PostLogin $postLogin = null, LdapUserProvider $ldapUserProvider = null)
    {
        $profileRepository = null;
        $postLogin = null;

        if ($doctrineRegistry === null) {
            $profileRepository = $doctrineRegistry->getRepository('MealzUserBundle:Profile');
        }

        if ($postLogin === null) {
            $postLogin = new PostLogin($doctrineRegistry->getEntityManager(), new Logger('PostLogin'));
        }

        global $kernel;
        $container = $kernel->getContainer();
        /*
                LdapClientInterface $ldap,
                $baseDn,
                $searchDn = null,
                $searchPassword = null,
                array $defaultRoles = array(),
                $uidKey = 'sAMAccountName',
                $filter = '({uid_key}={username})',
                PostLogin $postLogin
                */
        var_dump(get_class_methods($doctrineRegistry));
        var_dump(get_class_methods($container));
        var_dump(get_class_methods($this));
        var_dump(get_class_methods($doctrineRegistry->getEntityManager()->getEventManager()));

        if ($ldapUserProvider === null) {
            $ldapUserProvider = new LdapUserProvider(
                $kernel->getContainer()->getDefinition('security.user.provider.ldap'),
                $container->getDefinition('security.providers.active_directory.ldap.base_dn'),
                $doctrineRegistry->getDefinition('security.providers.active_directory.ldap.search_dn'),
                $doctrineRegistry->getDefinition('security.providers.active_directory.ldap.search_password'),
                $doctrineRegistry->getDefinition('security.providers.active_directory.ldap.default_roles'),
                'sAMAccountName',
                '({uid_key}={username})',
                $postLogin
            );
        }

        $this->profileRepository = $profileRepository;
        $this->postLogin = $postLogin;
        $this->ldapUserProvider = $ldapUserProvider;
    }

    /**
     * @param string $username
     * @return Profile
     */
    public function loadUserByUsername($username)
    {
        var_dump($username);
        $user = $this->profileRepository->findByUsername($username);
        if (false === $user instanceof Mealz\UserBundle\Entity\Profile) {
            throw new UsernameNotFoundException('user with username '.$username.' not found');
        }
        return $user;
    }

    /**
     * @param $username
     * @param $id
     *
     * @return User
     */
    public function loadUserByIdOrCreate($username, $id)
    {
        var_dump($username);
        var_dump($id);
        $dbUser = $this->userRepository->findById($id);
        if ($dbUser === null) {
            $ldapUser = new LdapUser(
                $user['samaccountname'][0],
                $user['displayname'][0],
                $user['givenname'][0],
                $user['sn'][0],
                $roles
            );
            $this->postLogin->assureProfileExists($ldapUser);
            return $user;
        }
        return $dbUser;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        return $this->loadUserByIdOrCreate($response->getNickname(), $response->getResponse()['sub']);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
        }

        return $this->loadUserByIdOrCreate($user->getUsername(), $user->getId());
    }
}
