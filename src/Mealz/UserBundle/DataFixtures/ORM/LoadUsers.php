<?php

namespace Mealz\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mealz\UserBundle\Entity\Login;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Mealz\UserBundle\Entity\Profile;


class LoadUsers extends AbstractFixture implements OrderedFixtureInterface,ContainerAwareInterface {

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
		$this->addUser('kochomi');

		for ($i = 0; $i < 100; $i++) {
			$this->addUser(uniqid());
		}

		$this->objectManager->flush();
	}

	protected function addUser($name) {
		$login = new Login();
		$login->setUsername($name);
		$login->setSalt(md5(uniqid(null, true)));

		/** @var EncoderFactory $encoder */
		$encoder = $this->container->get('security.password_encoder');
		$login->setPassword($encoder->encodePassword($login, $login->getSalt()));

		$profile = new Profile();
		$profile->setUsername($name);
		$profile->setName($name);
		$profile->setFirstName(strrev($name));
		$login->setProfile($profile);

		$this->objectManager->persist($profile);
		$this->objectManager->persist($login);

		$this->addReference('profile-' . $this->counter++, $profile);
		$this->addReference('login-' . $this->counter, $login);
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

    /**
     * LoadUsers constructor.
     * @param ContainerInterface $container
     */
	public function __construct(ContainerInterface $container)
	{
		$this->container = $container;
	}
}