<?php

namespace App\Mealz\MealBundle\Service\External\OAuthServer;

use Doctrine\ORM\EntityManagerInterface;

class AccessTokenManager extends \FOS\OAuthServerBundle\Entity\AccessTokenManager
{
    public function __construct(EntityManagerInterface $em, string $class)
    {
        $this->em = $em;
        $this->repository = $em->getRepository($class);
        $this->class = $class;
    }
}
