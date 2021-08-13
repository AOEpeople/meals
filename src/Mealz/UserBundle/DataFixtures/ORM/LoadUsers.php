<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Mealz\UserBundle\Entity\Login;
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

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->passwordEncoder = $encoder;
    }

    /**
     * @inheritDoc
     */
    public function load(ObjectManager $manager): void
    {
        $this->objectManager = $manager;
        $users = [
            'alice'   => ['roles' => ['ROLE_USER']],
            'bob'     => ['roles' => ['ROLE_USER']],
            'finance' => ['roles' => ['ROLE_USER']],
            'jane'    => ['roles' => ['ROLE_USER']],
            'john'    => ['roles' => ['ROLE_USER']],
            'kochomi' => ['roles' => ['ROLE_KITCHEN_STAFF']],
        ];

        foreach ($users as $username => $attrs) {
            $this->addUser($username, $attrs['roles']);
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
    protected function addUser(string $username, array $roles): void
    {
        $login = new Login();
        $login->setUsername($username);
        $login->setSalt(md5(uniqid('', true)));

        $hashedPassword = $this->passwordEncoder->encodePassword($login, $username);
        $login->setPassword($hashedPassword);

        $profile = new Profile();
        $profile->setUsername($username);
        $profile->setName(strrev($username));
        $profile->setFirstName($username);

        // set roles
        $roleObjs = [];
        foreach ($roles as $role) {
            $roleObjs[] = $this->getReference($role);
        }

        $profile->setRoles(new ArrayCollection($roleObjs));
        $login->setProfile($profile);

        $this->objectManager->persist($profile);
        $this->objectManager->persist($login);

        $this->addReference('profile-'.$this->counter++, $profile);
        $this->addReference('login-'.$this->counter, $login);
    }
}
