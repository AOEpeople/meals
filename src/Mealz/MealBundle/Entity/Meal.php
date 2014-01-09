<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Meal
 *
 * @ORM\Table(name="meal")
 * @ORM\Entity
 */
class Meal
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
	 * @ORM\ManyToOne(targetEntity="Dish")
	 * @ORM\JoinColumn(name="dish_id", referencedColumnName="id")
	 * @var Dish
	 */
	protected $dish;

	/**
	 * @ORM\Column(type="datetime", nullable=FALSE)
	 * @var \DateTime
	 */
	protected $dateTime;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="Participant", mappedBy="meal")
	 */
	protected $participants;

	public function __construct() {
		$this->participants = new ArrayCollection();
	}

	/**
	 * Get id
	 *
	 * @return integer
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * @param \DateTime $dateTime
	 */
	public function setDateTime($dateTime)
	{
		$this->dateTime = $dateTime;
	}

	/**
	 * @return \DateTime
	 */
	public function getDateTime()
	{
		return $this->dateTime;
	}

	/**
	 * @param \Mealz\MealBundle\Entity\Dish $dish
	 */
	public function setDish($dish)
	{
		$this->dish = $dish;
	}

	/**
	 * @return \Mealz\MealBundle\Entity\Dish
	 */
	public function getDish()
	{
		return $this->dish;
	}

	/**
	 * @return \Doctrine\Common\Collections\ArrayCollection
	 */
	public function getParticipants()
	{
		return $this->participants;
	}

	function __toString() {
		return $this->getDateTime()->format('Y-m-d H:i:s') . ' ' . $this->getDish();
	}


}
