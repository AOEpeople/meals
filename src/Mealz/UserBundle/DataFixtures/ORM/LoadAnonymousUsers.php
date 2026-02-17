<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\DataFixtures\ORM;

use App\Mealz\UserBundle\Entity\Login;
use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Override;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

/**
 * @psalm-suppress PropertyNotSetInConstructor
 */
final class LoadAnonymousUsers extends Fixture implements OrderedFixtureInterface
{
    /**
     * Constant to declare load order of fixture.
     */
    private const int ORDER_NUMBER = 10;

    protected EntityManagerInterface $objectManager;

    protected UserPasswordHasherInterface $passwordHasher;

    protected int $counter = 0;

    public function __construct(UserPasswordHasherInterface $encoder)
    {
        $this->passwordHasher = $encoder;
    }

    /**
     * Load the Fixtures for Prod DB data.
     *
     * @throws Exception
     */
    #[Override]
    public function load(ObjectManager $manager): void
    {
        if (!$manager instanceof EntityManagerInterface) {
            throw new Exception('Expected EntityManagerInterface, got ' . get_class($manager));
        }

        $this->objectManager = $manager;

        $connection = $this->objectManager->getConnection();
        $connection->beginTransaction();

        // List of protected Users, which should not be touched
        $protectedUsers = [
            'alice.meals',
            'bob.meals',
            'finance.meals',
            'jane.meals',
            'john.meals',
            'kochomi.meals',
            'admin.meals',
        ];

        try {
            // disable consistency check (We need because dependet foreign and primary keys)
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS=0;');

            // Anonymize all participant, guest_invitation, transaction and profile Names
            foreach ($this->getAllUsers() as $key => $user) {
                if (true === in_array($user->getUsername(), $protectedUsers, true)) {
                    continue;
                }

                $connection->executeQuery(
                    "UPDATE participant SET profile_id='" . $key . "XXX' WHERE profile_id='" . $user->getUsername() . "';"
                );
                $connection->executeQuery(
                    "UPDATE guest_invitation SET host_id='" . $key . "XXX' WHERE host_id='" . $user->getUsername() . "';"
                );
                $connection->executeQuery(
                    "UPDATE transaction SET profile='" . $key . "XXX' WHERE profile='" . $user->getUsername() . "';"
                );
                $connection->executeQuery(
                    "UPDATE profile SET id='" . $key . "XXX', name='" . $key . "Surname', firstname='" . $key . "Firstname' " .
                    "WHERE id='" . $user->getUsername() . "';"
                );
            }

            // write in Database and close the connection
            $this->objectManager->flush();
            $connection->commit();

            // create a new Connection
            $connection = $this->objectManager->getConnection();
            $connection->beginTransaction();

            foreach ($this->getAllUsers() as $key => $user) {
                if (true === in_array($user->getUsername(), $protectedUsers, true)) {
                    continue;
                }

                $anonymousUsername = $key . 'XXX';
                $login = new Login();
                $login->setUsername($anonymousUsername);
                $this->objectManager->persist($user);

                $login->setProfile($user);

                $hashedPassword = $this->passwordHasher->hashPassword($login, $anonymousUsername);
                $login->setPassword($hashedPassword);
                $this->objectManager->persist($login);
            }

            /* enable consistency checks */
            $connection->executeQuery('SET FOREIGN_KEY_CHECKS=1;');

            /* commit DB changes and close Connection */
            $this->objectManager->flush();
            $connection->commit();
        } catch (Exception $e) {
            $connection->rollback();
            $connection->close();
            throw $e;
        }
    }

    #[Override]
    public function getOrder(): int
    {
        // load as tenth
        return self::ORDER_NUMBER;
    }

    /**
     * @return list<Profile>
     */
    protected function getAllUsers(): array
    {
        return $this->objectManager->getRepository(Profile::class)->findAll();
    }
}
