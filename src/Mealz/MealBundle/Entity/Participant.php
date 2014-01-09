<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mealz\UserBundle\Entity\Zombie;

/**
 * Dish
 *
 * @ORM\Table(name="participant")
 * @ORM\Entity
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
	 * @ORM\ManyToOne(targetEntity="Meal")
	 * @ORM\JoinColumn(name="meal_id", referencedColumnName="id")
	 * @var Meal
	 */
	protected $meal;

	/**
	 * @ORM\ManyToOne(targetEntity="Mealz\UserBundle\Entity\Zombie")
	 * @ORM\JoinColumn(name="user_id", referencedColumnName="id")
	 * @var Zombie
	 */
	protected $user;

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
	 * @param \Mealz\UserBundle\Entity\Zombie $user
	 */
	public function setUser($user)
	{
		$this->user = $user;
	}

	/**
	 * @return \Mealz\UserBundle\Entity\Zombie
	 */
	public function getUser()
	{
		return $this->user;
	}


	function __toString()
	{
		return $this->getMeal() . ' ' . $this->getUser();
	}


}
