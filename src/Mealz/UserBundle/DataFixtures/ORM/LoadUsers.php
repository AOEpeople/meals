<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Mealz\UserBundle\Entity\Profile;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Loads users.
 */
class LoadUsers extends Fixture implements OrderedFixtureInterface
{
    /**
     * Constant to declare load order of fixture
     */
    private const ORDER_NUMBER = 1;

    protected ObjectManager $objectManager;

    protected UserPasswordEncoderInterface $passwordEncoder;

    protected int $counter = 0;

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager): void
    {
        $this->objectManager = $manager;
        $users = [
            ['username' => 'alice.meals', 'firstName' => 'Alice', 'lastName' => 'Meals', 'roles' => ['ROLE_USER']],
            ['username' => 'bob.meals', 'firstName' => 'Bob', 'lastName' => 'Meals', 'roles' => ['ROLE_USER']],
            ['username' => 'finance.meals', 'firstName' => 'Finance', 'lastName' => 'Meals', 'roles' => ['ROLE_FINANCE']],
            ['username' => 'jane.meals', 'firstName' => 'Jane', 'lastName' => 'Meals', 'roles' => ['ROLE_USER']],
            ['username' => 'john.meals', 'firstName' => 'John', 'lastName' => 'Meals', 'roles' => ['ROLE_USER']],
            ['username' => 'kochomi.meals', 'firstName' => 'kochomi', 'lastName' => 'Meals', 'roles' => ['ROLE_KITCHEN_STAFF']],
        ];

        foreach ($users as $user) {
            $this->addUser($user['username'], $user['firstName'], $user['lastName'], $user['roles']);
        }

        $this->objectManager->flush();
    }

    /**
     * Get the Fixtures loadOrder
     */
    public function getOrder(): int
    {
        // load as first
        return self::ORDER_NUMBER;
    }

    /**
     * @param string[] $roles List of role identifiers
     */
    protected function addUser(
        string $username,
        string $firstName,
        string $lastName,
        array $roles
    ): void {
        $profile = new Profile();
        $profile->setUsername($username);
        $profile->setName($lastName);
        $profile->setFirstName($firstName);

        // set roles
        $roleObjs = [];
        foreach ($roles as $role) {
            $roleObjs[] = $this->getReference($role);
        }

        $profile->setRoles(new ArrayCollection($roleObjs));
        $this->objectManager->persist($profile);

        $this->addReference('profile-'.$this->counter++, $profile);
    }
}
