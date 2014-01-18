<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mealz\UserBundle\Entity\User;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Dish
 *
 * @ORM\Table(name="participant")
 * @ORM\Entity()
 */
class Participant
{
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	private $id;

	/**
	 * @Assert\NotNull()
	 * @Assert\Type(type="Mealz\MealBundle\Entity\Meal")
	 * @ORM\ManyToOne(targetEntity="Meal",inversedBy="participants")
	 * @ORM\JoinColumn(name="meal_id", referencedColumnName="id")
	 * @var Meal
	 */
	protected $meal;

	/**
	 * @Assert\NotNull()
	 * @Assert\Type(type="Mealz\UserBundle\Entity\User")
	 * @ORM\ManyToOne(targetEntity="Mealz\UserBundle\Entity\User")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
	 * @var User
	 */
	protected $user;

	/**
	 * @Assert\Length(min=3, max=2048)
	 * @ORM\Column(type="string", length=2048, nullable=TRUE)
	 * @var string
	 */
	protected $comment;

	/**
	 * @Assert\Length(min=3, max=255)
	 * @ORM\Column(type="string", length=255, nullable=TRUE)
	 * @var string
	 */
	protected $guestName;

	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param \Mealz\MealBundle\Entity\Meal $meal
	 */
	public function setMeal($meal)
	{
		$this->meal = $meal;
	}

	/**
	 * @return \Mealz\MealBundle\Entity\Meal
	 */
	public function getMeal()
	{
		return $this->meal;
	}

	/**
	 * @param \Mealz\UserBundle\Entity\User $user
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}

	/**
	 * @return \Mealz\UserBundle\Entity\User
	 */
	public function getUser()
	{
		return $this->user;
	}

	/**
	 * @param string $comment
	 */
	public function setComment($comment)
	{
		$this->comment = $comment;
	}

	/**
	 * @return string
	 */
	public function getComment()
	{
		return $this->comment;
	}

	/**
	 * @param string $guestName
	 */
	public function setGuestName($guestName)
	{
		$this->guestName = $guestName ?: NULL;
	}

	/**
	 * @return bool
	 */
	public function isGuest() {
		return (bool)$this->getGuestName();
	}

	/**
	 * @return string
	 */
	public function getGuestName()
	{
		return $this->guestName;
	}

	function __toString()
	{
		return $this->getMeal() . ' ' . $this->getUser();
	}

	public function __clone() {
		$this->id = NULL;
	}


}
