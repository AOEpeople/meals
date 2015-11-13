<?php

namespace Mealz\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\Role\Role;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * A profile is some kind of user record in the database that does not know anything about logins.
 *
 * The name "profile" was chosen because in Symfony a "User" is someone who is allowed to log in.
 *
 * @ORM\Table(name="profile")
 * @ORM\Entity
 */
class Profile {
	/**
	 * @var string
	 *
	 * @ORM\Column(name="id", type="string", length=255, nullable=FALSE)
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="NONE")
	 */
	private $username;

	/**
	 * @ORM\Column(type="string", length=255, nullable=TRUE)
	 * @var string
	 */
	protected $name;

	/**
	 * @ORM\OneToMany(targetEntity="Mealz\AccountingBundle\Entity\Transaction", mappedBy="user")
	 * @var ArrayCollection
	 */
	protected $transactions;

	/**
	 * @param string $username
	 */
	public function setUsername($username)
	{
		$this->username = $username;
	}

	/**
	 * @return string
	 */
	public function getUsername()
	{
		return $this->username;
	}

	/**
	 * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	public function __toString() {
		return $this->getUsername();
	}
}
