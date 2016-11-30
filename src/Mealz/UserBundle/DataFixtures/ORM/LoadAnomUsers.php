<?php

namespace Mealz\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Loads users.
 */
class LoadAnomUsers extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

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
     * Load the Fixtures
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->objectManager = $manager;

        $connection = $this->objectManager->getConnection();
        $connection->connect();
        $connection->beginTransaction();

        try {
            $connection->query('SET FOREIGN_KEY_CHECKS=0;');

            foreach ($this->getAllUsers() as $key => $user) {
                //$connection->beginTransaction();
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
                    "UPDATE profile SET id='".$key."XXX', name='".$key."Surname', firstname='".$key."Firstname' WHERE id='".$user->getUsername()."';"
                );
                //$connection->commit();
            }

            $connection->query('SET FOREIGN_KEY_CHECKS=1;');
            $connection->commit();
        } catch (\Exception $e) {
            $connection->rollback();
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
        return 10;
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

