<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Mealz\UserBundle\Entity\Role;

/**
 * Loads user roles.
 */
class LoadRoles extends Fixture
{
    /**
     * Constant to declare load order of fixture
     */
    private const ORDER_NUMBER = 1;

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager): void
    {
        foreach ($this->getRoles() as $role) {
            $roleObj = new Role();
            $roleObj->setTitle($role['title'])
                    ->setSid($role['sid']);
            $manager->persist($roleObj);

            $this->addReference($role['sid'], $roleObj);
        }

        $manager->flush();
    }

    /**
     * Gets the loading order of fixture.
     */
    public function getOrder(): int
    {
        return self::ORDER_NUMBER;
    }

    /**
     * Gets the test user roles to create.
     *
     * @return array<string,string>
     */
    protected function getRoles(): array
    {
        return [
            ['title' => 'Kitchen Staff', 'sid' => 'ROLE_KITCHEN_STAFF'],
            ['title' => 'User', 'sid' => 'ROLE_USER'],
            ['title' => 'Guest', 'sid' => 'ROLE_GUEST'],
            ['title' => 'Administrator', 'sid' => 'ROLE_ADMIN'],
            ['title' => 'Finance Staff', 'sid' => 'ROLE_FINANCE'],
        ];
    }
}
