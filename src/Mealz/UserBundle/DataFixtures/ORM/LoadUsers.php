<?php

namespace Mealz\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mealz\UserBundle\Entity\Login;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Mealz\UserBundle\Entity\Profile;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

/**
 * Loads users.
 */
class LoadUsers extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{
    /**
     * Constant to declare load order of fixture
     */
    const ORDER_NUMBER = 1;

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
     * LoadUsers constructor.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
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

        $this->addUser('alice');
        $this->addUser('bob');
        $this->addUser('john');
        $this->addUser('jane');
        $this->addUser('kochomi');

        $this->objectManager->flush();
    }

    /**
     * Get the Fixtures loadOrder
     * @return int
     */
    public function getOrder()
    {
        /**
         * load as first
         */
        return self::ORDER_NUMBER;
    }

    /**
     * @param string $name Username
     */
    protected function addUser($name)
    {
        $login = new Login();
        $login->setUsername($name);
        $login->setSalt(md5(uniqid(null, true)));

        /** TODO: sercurity.encoder_factory will be deprecated since symfony v3.x */
        /** @var PasswordEncoderInterface $encoder */
        $encoder = $this->container->get('security.encoder_factory')->getEncoder($login);
        $login->setPassword($encoder->encodePassword($name, $login->getSalt()));

        $profile = new Profile();
        $profile->setUsername($name);
        $profile->setName(strrev($name));
        $profile->setFirstName($name);
        $login->setProfile($profile);

        $this->objectManager->persist($profile);
        $this->objectManager->persist($login);

        $this->addReference('profile-'.$this->counter++, $profile);
        $this->addReference('login-'.$this->counter, $login);
    }

}

