<?php

namespace Mealz\UserBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

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
	 * @ORM\Column(type="string", length=255, nullable=TRUE)
	 * @var string
	 */
	protected $firstName;

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

	/**
	 * @return string
	 */
	public function getFirstName()
	{
		return $this->firstName;
	}

	/**
	 * @param string $firstName
	 */
	public function setFirstName($firstName)
	{
		$this->firstName = $firstName;
	}

	/**
	 * @return string
	 */
	public function getFullName()
	{
		return "$this->name, $this->firstName";
	}

	public function __toString() {
		return $this->getUsername();
	}
}
