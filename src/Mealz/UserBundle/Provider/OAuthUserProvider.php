<?php

/*
 * This file was part of the HWIOAuthBundle package and was edited for meals
 *
 * (c) Hardware.Info <opensource@hardware.info>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Mealz\UserBundle\Provider;

use \HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\OAuthAwareUserProviderInterface;
use Mealz\UserBundle\User\OAuthUser;
use Mealz\UserBundle\Entity\Profile;
use \Doctrine\Bundle\DoctrineBundle\Registry;

/**
 * OAuthUserProvider.
 *
 * @author Geoffrey Bachelet <geoffrey.bachelet@gmail.com>
 */
class OAuthUserProvider implements UserProviderInterface, OAuthAwareUserProviderInterface
{
    /**
     * @var ProfileRepository
     */
    private $profileRepository;

    /**
     * @var DoctrineRegistry
     */
    private $doctrineRegistry;

    /**
     * OAuthUserProvider constructor.
     *
     * @param      Registry  $doctrineRegistry
     */
    public function __construct(Registry $doctrineRegistry)
    {
        if ($doctrineRegistry !== null) {
            $profileRepository = $doctrineRegistry->getRepository('MealzUserBundle:Profile');
        }

        $this->profileRepository = $profileRepository;
        $this->doctrineRegistry = $doctrineRegistry;
    }

    /**
     * {@inheritdoc}
     *
     * @param      <type>     $username  The username
     *
     * @return     OAuthUser  ( description_of_the_return_value )
     */
    public function loadUserByUsername($username)
    {
        return new OAuthUser($username);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByIdOrCreate($userInformation)
    {
        /**
         * First Check if array Informations are given
         */
        if (gettype($userInformation) === 'object' &&
            property_exists($userInformation, 'preferred_username') === true &&
            property_exists($userInformation, 'family_name') === true &&
            property_exists($userInformation, 'given_name') === true
        ) {
            $profile = $this->doctrineRegistry->getManager()->find(
                'MealzUserBundle:Profile',
                $userInformation->preferred_username
            );
        } else {
            return false;
        }

        /**
         * if Userprofile is null, create User
         */
        if ($profile === null) {
            $profile = $this->createProfile(
                $userInformation->preferred_username,
                $userInformation->family_name,
                str_replace(' ', '', explode(',', $userInformation->given_name)[1])
            );
        }

        $user = new OAuthUser($userInformation->preferred_username);
        $user->setProfile($profile);

        /**
         * Map Keycloak Roles to Meals Roles
         */
        if (array_search('aoe_employee', $userInformation->realm_access->roles) !== false) {
            /**
             * give User the ROLE_OAUTH_USER Role to access meals
             * Use in Combination of /app/config/commons/all/security.yml
             */
            $user->addRole('ROLE_OAUTH_USER');
        }

        if (array_search('meals.admin', $userInformation->realm_access->roles) !== false) {
            /**
             * map Keycloak aoe.admin Role to meals ROLE_KITCHEN_STAFF
             */
            $user->addRole('ROLE_KITCHEN_STAFF');
        }

        if ($user instanceof Login) {
            $this->doctrineRegistry->getManager()->persist($user);
            $this->doctrineRegistry->getManager()->flush();
        }

        return $user;
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        /**
         * get OAuth User Token Informations
         */
        $accesTokens = explode('.', $response->getAccessToken());
        $userInformation = json_decode(base64_decode($accesTokens[1]));

        return $this->loadUserByIdOrCreate($userInformation);
    }

    /**
     * {@inheritdoc}
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user))) {
            throw new UnsupportedUserException(sprintf('Unsupported user class "%s"', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    /**
     * {@inheritdoc}
     */
    public function supportsClass($class)
    {
        return $class === 'Mealz\\UserBundle\\User\\OAuthUser';
    }

    /**
     * Creates a profile.
     *
     * @param      \Symfony\Component\Security\Core\User\UserInterface  $username
     * @param      String                                               $givenName  The given name
     * @param      String                                               $surName    The sur name
     *
     * @return     Profile
     */
    protected function createProfile($username, $givenName, $surName)
    {
        $profile = new Profile();
        $profile->setUsername($username);

        $profile->setName($surName);
        $profile->setFirstName($givenName);

        $this->doctrineRegistry->getManager()->persist($profile);
        $this->doctrineRegistry->getManager()->flush();

        return $profile;
    }
}
