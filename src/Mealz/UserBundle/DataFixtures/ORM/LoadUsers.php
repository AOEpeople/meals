<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\DataFixtures\ORM;

use App\Mealz\UserBundle\Entity\Login;
use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class LoadUsers extends Fixture implements OrderedFixtureInterface
{
    /**
     * Constant to declare load order of fixture.
     */
    private const ORDER_NUMBER = 1;

    protected ObjectManager $objectManager;

    protected UserPasswordEncoderInterface $passwordEncoder;

    protected int $counter = 0;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->passwordEncoder = $encoder;
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager): void
    {
        $this->objectManager = $manager;
        $users = [
            ['username' => 'alice.meals', 'password' => 'Chee7ieRahqu', 'firstName' => 'Alice', 'lastName' => 'Meals', 'roles' => ['ROLE_USER']],
            ['username' => 'bob.meals', 'password' => 'ON2za5OoJohn', 'firstName' => 'Bob', 'lastName' => 'Meals', 'roles' => ['ROLE_USER']],
            ['username' => 'finance.meals', 'password' => 'IUn4d9NKMt', 'firstName' => 'Finance', 'lastName' => 'Meals', 'roles' => ['ROLE_FINANCE']],
            ['username' => 'jane.meals', 'password' => 'heabahW6ooki', 'firstName' => 'Jane', 'lastName' => 'Meals', 'roles' => ['ROLE_USER']],
            ['username' => 'john.meals', 'password' => 'aef9xoo2hieY', 'firstName' => 'John', 'lastName' => 'Meals', 'roles' => ['ROLE_USER']],
            ['username' => 'kochomi.meals', 'password' => 'f8400YzaOd', 'firstName' => 'kochomi', 'lastName' => 'Meals', 'roles' => ['ROLE_KITCHEN_STAFF']],
        ];

        foreach ($users as $user) {
            $this->addUser($user['username'], $user['password'], $user['firstName'], $user['lastName'], $user['roles']);
        }

        $this->objectManager->flush();
    }

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
        string $password,
        string $firstName,
        string $lastName,
        array $roles
    ): void {
        $login = new Login();
        $login->setUsername($username);
        $login->setSalt(md5(uniqid('', true)));

        $hashedPassword = $this->passwordEncoder->encodePassword($login, $password);
        $login->setPassword($hashedPassword);

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
        $login->setProfile($profile);

        $this->objectManager->persist($profile);
        $this->objectManager->persist($login);

        $this->addReference('profile-' . $this->counter++, $profile);
        $this->addReference('login-' . $this->counter, $login);
    }
}
