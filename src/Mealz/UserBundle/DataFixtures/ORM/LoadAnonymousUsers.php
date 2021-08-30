<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\DataFixtures\ORM;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Mealz\UserBundle\Entity\Login;
use Exception;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Loads users.
 */
class LoadAnonymousUsers extends Fixture implements OrderedFixtureInterface
{
    /**
     * Constant to declare load order of fixture
     */
    private const ORDER_NUMBER = 10;

    protected ObjectManager $objectManager;

    protected UserPasswordEncoderInterface $passwordEncoder;

    protected int $counter = 0;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
        $this->passwordEncoder = $encoder;
    }

    /**
     * Load the Fixtures for Prod DB data
     *
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $this->objectManager = $manager;

        $connection = $this->objectManager->getConnection();
        $connection->connect();
        $connection->beginTransaction();

        // List of protected Users, which should not be touched
        $protectedUsers = ['alice.meals', 'bob.meals', 'finance.meals', 'jane.meals', 'john.meals', 'kochomi.meals'];

        try {
            // disable consistency check (We need because dependet foreign and primary keys)
            $connection->query('SET FOREIGN_KEY_CHECKS=0;');

            // Anonymize all participant, guest_invitation, transaction and profile Names
            foreach ($this->getAllUsers() as $key => $user) {
                if (true === in_array($user->getUsername(), $protectedUsers, true)) {
                    continue;
                }

                $connection->query(
                    "UPDATE participant SET profile_id='".$key."XXX' WHERE profile_id='".$user->getUsername()."';"
                );
                $connection->query(
                    "UPDATE guest_invitation SET host_id='".$key."XXX' WHERE host_id='".$user->getUsername()."';"
                );
                $connection->query(
                    "UPDATE transaction SET profile='".$key."XXX' WHERE profile='".$user->getUsername()."';"
                );
                $connection->query(
                    "UPDATE profile SET id='".$key."XXX', name='".$key."Surname', firstname='".$key."Firstname' " .
                    "WHERE id='".$user->getUsername()."';"
                );
            }

            // write in Database and close the connection
            $this->objectManager->flush();
            $connection->commit();

            // create a new Connection
            $connection = $this->objectManager->getConnection();
            $connection->connect();
            $connection->beginTransaction();

            foreach ($this->getAllUsers() as $key => $user) {
                if (true === in_array($user->getUsername(), $protectedUsers, true)) {
                    continue;
                }

                $anonymousUsername = $key.'XXX';
                $login = new Login();
                $login->setUsername($anonymousUsername);
                $this->objectManager->persist($user);

                $login->setProfile($user);
                $login->setSalt(md5(uniqid('', true)));

                $hashedPassword = $this->passwordEncoder->encodePassword($login, $anonymousUsername);
                $login->setPassword($hashedPassword);
                $this->objectManager->persist($login);
            }

            /** enable consistency checks */
            $connection->query('SET FOREIGN_KEY_CHECKS=1;');

            /** commit DB changes and close Connection */
            $this->objectManager->flush();
            $connection->commit();
        } catch (Exception $e) {
            $connection->rollback();
            $connection->close();
            throw $e;
        }
    }

    /**
     * Get the Fixtures loadOrder
     */
    public function getOrder(): int
    {
        // load as tenth
        return self::ORDER_NUMBER;
    }

    protected function getAllUsers(): array
    {
        return $this->objectManager->getRepository('MealzUserBundle:Profile')->findAll();
    }
}
