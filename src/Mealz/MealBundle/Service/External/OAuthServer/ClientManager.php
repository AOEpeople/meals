<?php

namespace App\Mealz\MealBundle\Service\External\OAuthServer;

use Doctrine\ORM\EntityManagerInterface;

class ClientManager extends \FOS\OAuthServerBundle\Entity\ClientManager
{
    public function __construct(EntityManagerInterface $em, $class)
    {
        $this->em = $em;
        $this->repository = $em->getRepository($class);
        $this->class = $class;
    }
}
