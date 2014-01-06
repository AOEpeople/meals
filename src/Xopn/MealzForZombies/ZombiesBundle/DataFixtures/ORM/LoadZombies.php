<?php

namespace Xopn\MealzForZombies\ZombiesBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Xopn\MealzForZombies\ZombiesBundle\Entity\Zombie;


class LoadZombies extends AbstractFixture implements OrderedFixtureInterface,ContainerAwareInterface {

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

    function load(ObjectManager $manager) {
        $this->objectManager = $manager;

        $this->addUser('alice');
        $this->addUser('bob');
        $this->addUser('john');
        $this->addUser('jane');

        $this->objectManager->flush();
    }

    protected function addUser($name) {
        $user = new Zombie();
        $user->setUsername($name);
        $user->setSalt(md5(uniqid(null, true)));

        /** @var EncoderFactory $encoder */
        $encoder = $this->container->get('security.encoder_factory')->getEncoder($user);
        $user->setPassword($encoder->encodePassword($name, $user->getSalt()));

        $this->objectManager->persist($user);
        $this->addReference('user-' . $this->counter++, $user);
    }

    public function getOrder()
    {
        return 1;
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
}