<?php

namespace Mealz\UserBundle\User;

use Mealz\UserBundle\Entity\Profile;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

/**
 * logged in users in this application should be able to have a profile
 */
interface OAuthUserInterface extends SymfonyUserInterface, EquatableInterface, \Serializable, UserInterface
{
    public function __toString();
}
