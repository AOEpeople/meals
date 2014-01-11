<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mealz\UserBundle\Entity\Zombie;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Dish
 *
 * @ORM\Table(name="participant")
 * @ORM\Entity(repositoryClass="Mealz\MealBundle\Entity\ParticipantRepository")
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
	 * @ORM\ManyToOne(targetEntity="Meal",inversedBy="participants")
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
	 * @Assert\Length(min=3, max=2048)
	 * @ORM\Column(type="string", length=2048, nullable=TRUE)
	 * @var string
	 */
	protected $comment;

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

	function __toString()
	{
		return $this->getMeal() . ' ' . $this->getUser();
	}


}
