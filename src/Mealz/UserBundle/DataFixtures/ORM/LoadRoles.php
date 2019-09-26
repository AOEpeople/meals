<?php

namespace Mealz\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Mealz\UserBundle\Entity\Role;

/**
 * Loads user roles.
 *
 * @author Chetan Thapliyal <chetan.thapliyal@aoe.com>
 */
class LoadRoles extends AbstractFixture
{
    /**
     * Constant to declare load order of fixture
     */
    const ORDER_NUMBER = 1;

    /**
     * Loads the roles fixture.
     *
     * @param ObjectManager $objectManager
     */
    public function load(ObjectManager $objectManager)
    {
        foreach ($this->getRoles() as $role) {
            $roleObj = new Role();
            $roleObj->setTitle($role['title'])
                    ->setSid($role['sid']);
            $objectManager->persist($roleObj);

            $this->addReference($role['sid'], $roleObj);
        }

        $objectManager->flush();
    }

    /**
     * Gets the loading order of fixture.
     *
     * @return int
     */
    public function getOrder()
    {
        return self::ORDER_NUMBER;
    }

    /**
     * Gets the test user roles to create.
     *
     * @return array
     */
    protected function getRoles()
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
