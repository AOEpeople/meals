<?php

namespace App\Mealz\MealBundle\Service\External\OAuthServer;

use Doctrine\ORM\EntityManagerInterface;

class AuthCodeManager extends \FOS\OAuthServerBundle\Entity\AuthCodeManager
{
    public function __construct(EntityManagerInterface $em, string $class)
    {
        $this->em = $em;
        $this->class = $class;
    }
}
