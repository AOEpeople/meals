<?php

namespace Mealz\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mealz\UserBundle\Entity\Login;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Loads users.
 * @Simon.Rininsland
 */
class LoadAnomUsers extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * Constant to declare load order of fixture
     */
    const ORDER_NUMBER = 10;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Container
     */
    protected $container;

    /**
     * @var int
     */
    protected $counter = 0;

    /**
     * @var AbstractFixture
     */
    protected $em;

    /**
     * LoadAnomUsers constructor.
     * @param ContainerInterface|null $container
     */
    public function __construct(ContainerInterface $container = null)
    {
        if ($container instanceof ContainerInterface) {
            $this->container = $container;
        }
    }

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }


    /**
     * Load the Fixtures for Prod DB data
     * @param ObjectManager $manager
     * @throws \Exception
     */
    public function load(ObjectManager $manager)
    {
        /**
         * a new Object Manager
         */
        $this->objectManager = $manager;

        /**
         * creating a new Connection
         */
        $connection = $this->objectManager->getConnection();
        $connection->connect();
        $connection->beginTransaction();

        /**
         * List of protected Users, which should not be touched
         */
        $protectedUsers = array('alice', 'bob', 'john', 'jane', 'kochomi', 'finance');

        try {
            /**
             * disabling Consisty Check (We need because dependet foreign and primary keys)
             */
            $connection->query('SET FOREIGN_KEY_CHECKS=0;');

            /**
             * Anonymize all participant, guest_invitation, transaction and profile Names
             */
            foreach ($this->getAllUsers() as $key => $user) {
                if (!in_array($user->getUsername(), $protectedUsers)) {
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
                        "UPDATE profile SET id='".$key."XXX', name='".$key."Surname', firstname='".$key."Firstname' WHERE id='".$user->getUsername(
                        )."';"
                    );
                }
            }

            /**
             * write in Database and close the connection
             */
            $this->objectManager->flush();
            $connection->commit();

            /**
             * create a new Connection
             */
            $connection = $this->objectManager->getConnection();
            $connection->connect();
            $connection->beginTransaction();

            /**
             * For each User
             */
            foreach ($this->getAllUsers() as $key => $user) {
                if (!in_array($user->getUsername(), $protectedUsers)) {
                    /**
                     * Generate Users Logins
                     */
                    $login = new Login();
                    $login->setUsername($key.'XXX');
                    $this->objectManager->persist($user);

                    $login->setProfile($user);
                    $login->setSalt(md5(uniqid(null, true)));

                    /** TODO: sercurity.encoder_factory will be deprecated since symfony v3.x */
                    /** @var PasswordEncoderInterface $encoder */
                    $encoder = $this->container->get('security.encoder_factory')->getEncoder($login);
                    $login->setPassword($encoder->encodePassword($key.'XXX', $login->getSalt()));
                    $this->objectManager->persist($login);
                }
            }

            /**
             * enabling Consisty Checks
             */
            $connection->query('SET FOREIGN_KEY_CHECKS=1;');

            /**
             * Commit DB changes and close Connection
             */
            $this->objectManager->flush();
            $connection->commit();
        } catch (\Exception $e) {
            /**
             * if an Error occurs do a Rollback
             */
            $connection->rollback();

            /**
             * and close the open connection
             */
            $connection->close();
            throw $e;
        }
    }

    /**
     * Get the Fixtures loadOrder
     * @return int
     */
    public function getOrder()
    {
        /**
         * load as tenth
         */
        return self::ORDER_NUMBER;
    }

    /**
     * @return array
     */
    protected function getAllUsers()
    {
        return $this->objectManager
            ->getRepository('MealzUserBundle:Profile')->findAll();
    }

}

