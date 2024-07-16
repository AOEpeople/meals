<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\DataFixtures\ORM;

use App\Mealz\UserBundle\Entity\Login;
use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\UserBundle\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
class LoadUsers extends Fixture implements OrderedFixtureInterface
{
    /**
     * Constant to declare load order of fixture.
     */
    private const int ORDER_NUMBER = 1;

    protected ObjectManager $objectManager;

    protected UserPasswordHasherInterface $passwordHasher;

    protected int $counter = 0;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $this->objectManager = $manager;
        $users = [
            [
                'username' => 'alice.meals',
                'password' => 'Chee7ieRahqu',
                'firstName' => 'Alice',
                'lastName' => 'Meals',
                'roles' => ['ROLE_USER'],
                'email' => 'alice.meals@aoe.com',
            ],
            [
                'username' => 'bob.meals',
                'password' => 'ON2za5OoJohn',
                'firstName' => 'Bob',
                'lastName' => 'Meals',
                'roles' => ['ROLE_USER'],
                'email' => 'bob.meals@aoe.com',
            ],
            [
                'username' => 'finance.meals',
                'password' => 'IUn4d9NKMt',
                'firstName' => 'Finance',
                'lastName' => 'Meals',
                'roles' => ['ROLE_FINANCE'],
                'email' => 'finance.meals@aoe.com',
            ],
            [
                'username' => 'jane.meals',
                'password' => 'heabahW6ooki',
                'firstName' => 'Jane',
                'lastName' => 'Meals',
                'roles' => ['ROLE_USER'],
                'email' => 'jane.meals@aoe.com',
            ],
            [
                'username' => 'john.meals',
                'password' => 'aef9xoo2hieY',
                'firstName' => 'John',
                'lastName' => 'Meals',
                'roles' => ['ROLE_USER'],
                'email' => 'john.meals@aoe.com',
            ],
            [
                'username' => 'kochomi.meals',
                'password' => 'f8400YzaOd',
                'firstName' => 'Kochomi',
                'lastName' => 'Meals',
                'roles' => ['ROLE_KITCHEN_STAFF'],
                'email' => 'kochomi.meals@aoe.com',
            ],
            [
                'username' => 'admin.meals',
                'password' => 'x3pAsFoq8d',
                'firstName' => 'Admin',
                'lastName' => 'Meals',
                'roles' => ['ROLE_ADMIN'],
                'email' => 'admin.meals@aoe.com',
            ],
        ];

        foreach ($users as $user) {
            $this->addUser($user['username'], $user['password'], $user['firstName'], $user['lastName'], $user['email'], $user['roles']);
        }

        for ($i = 0; $i < 15; ++$i) {
            $this->createRandUser();
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
        string $email,
        array $roles,
        bool $isRandUser = false,
    ): void {
        $login = new Login();
        $login->setUsername($username);

        $environment = getenv('APP_ENV');
        if (false === $isRandUser && 'prod' !== $environment && 'staging' !== $environment) {
            $hashedPassword = $this->passwordHasher->hashPassword($login, $password);
            $login->setPassword($hashedPassword);
        } else {
            $login->setPassword($password);
        }

        $profile = new Profile();
        $profile->setUsername($username);
        $profile->setName($lastName);
        $profile->setFirstName($firstName);
        $profile->setEmail($username);


        // set roles
        /** @var Role[] $roleObjs */
        $roleObjs = [];
        foreach ($roles as $role) {
            $roleObjs[] = $this->getReference($role, Role::class);
        }

        $profile->setRoles(new ArrayCollection($roleObjs));
        $login->setProfile($profile);

        $this->objectManager->persist($profile);
        $this->objectManager->persist($login);

        $this->addReference('profile-' . $this->counter++, $profile);
        $this->addReference('login-' . $this->counter, $login);
    }

    protected function createRandUser(): void
    {
        $firstNames = [
            'Felix', 'Maximilian', 'Alexander', 'Paul', 'Elias', 'Ben',
            'Noah', 'Leon', 'Louis', 'Jonas', 'Marie', 'Sophie',
            'Marian', 'Sophia', 'Emilia', 'Emma', 'Hannah', 'Anna',
            'Mia', 'Luisa', 'Lukas', 'Tim', 'Niklas', 'Jan',
            'Daniel', 'Kevin', 'Tobias', 'Philipp', 'Michael', 'Dennis',
            'Maria', 'Anne', 'Laura', 'Michelle', 'Lea', 'Julia',
            'Sarah', 'Lisa', 'Vanessa', 'Katharina', 'Noah', 'Matteo',
            'Finn', 'Emil', 'Luca', 'Henry', 'Christian', 'Sebastian',
            'Stefan', 'Benjamin', 'Lina', 'Mila', 'Ella', 'Klara',
            'Stefanie', 'Kathrin', 'Melanie', 'Nadine', 'Nicole', 'Sandra',
        ];
        $lastNames = [
            'Schmidt', 'Müller', 'Meyer', 'Schulz', 'Schneider', 'Hoffmann',
            'Becker', 'Fischer', 'Wagner', 'Weber', 'Bauer', 'Lange',
            'Wolf', 'Schäfer', 'Koch', 'Richter', 'Klein', 'Schröder',
            'Neumann', 'Schwarz', 'Zimmermann', 'Braun', 'Krüger', 'Hartmann',
            'Schmitt', 'Werner', 'Schmitz', 'Krause', 'Meier', 'Lehmann',
            'Köhler', 'Herrmann', 'König', 'Huber', 'Kaiser', 'Fuchs',
            'Peters', 'Lang', 'Möller', 'Weiß', 'Jung', 'Hahn', 'Friedrich',
            'Vogel',
        ];

        $randFirstName = $firstNames[array_rand($firstNames)];
        $randLastName = $lastNames[array_rand($lastNames)];
        $randPass = (string) rand();
        $username = strtolower($randFirstName) . '.' . strtolower($randLastName) . '.' . rand();
        $email = $username . '@aoe.com';
        $this->addUser(
            $username,
            $randPass,
            $randFirstName,
            $randLastName,
            $email,
            ['ROLE_USER'],
            true,
        );
    }
}
