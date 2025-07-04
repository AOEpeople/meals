<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\DataFixtures\ORM;

use App\Mealz\UserBundle\Entity\Role;
use App\Mealz\UserBundle\Service\RoleProvider;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class LoadRoles extends Fixture
{
    /**
     * Constant to declare load order of fixture.
     */
    private const ORDER_NUMBER = 1;

    public function load(ObjectManager $manager): void
    {
        $roleProvider = new RoleProvider();
        foreach ($roleProvider->getRoles() as $role) {
            $roleObj = new Role();
            $roleObj->setTitle($role['title'])
                    ->setSid($role['sid']);
            $manager->persist($roleObj);

            $this->addReference($role['sid'], $roleObj);
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        return self::ORDER_NUMBER;
    }
}
