<?php

namespace Mealz\UserBundle\Provider;

use Mealz\UserBundle\Entity\Profile;
use Mealz\UserBundle\Entity\Profile\ProfileRepository;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Class OAuthUserProvider
 */
class OAuthUserProvider extends \HWI\Bundle\OAuthBundle\Security\Core\User\OAuthUserProvider
{

    /**
     * @var ProfileRepository
     */
    private $profileRepository;

    /**
     * OAuthUserProvider constructor.
     *
     * @param \Doctrine\Bundle\DoctrineBundle\Registry $doctrineRegistry
     */
    public function __construct(\Doctrine\Bundle\DoctrineBundle\Registry $doctrineRegistry = null)
    {
        $profileRepository = null;
        if ($doctrineRegistry !== null) {
            $profileRepository = $doctrineRegistry->getRepository('MealzUserBundle:Profile');
        }
        $this->profileRepository = $profileRepository;
    }

    /**
     * @param string $username
     * @return Profile
     */
    public function loadUserByUsername($username)
    {
        $user = $this->profileRepository->findByUsername($username);
        if (false === $user instanceof Profile) {
            throw new Exception(sprintf('user with username %s not found'), $username);
        }
        return $user;
    }
}
