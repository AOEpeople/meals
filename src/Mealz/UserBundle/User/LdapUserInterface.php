<?php

namespace App\Mealz\UserBundle\User;

use App\Mealz\UserBundle\Entity\Profile;
use Serializable;
use Symfony\Component\Security\Core\User\EquatableInterface;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUserInterface;

/**
 * logged in users in this application should be able to have a profile
 */
interface LdapUserInterface extends SymfonyUserInterface, EquatableInterface, Serializable, UserInterface
{
    public function getEmail();
    public function setEmail($email);

    public function getDn();
    /**
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function setDn($dn);

    public function getCn();

    /**
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    public function setCn($cn);

    public function getAttributes();
    public function setAttributes(array $attributes);
    public function getAttribute($name);

    public function __toString();
}
