<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Mealz\UserBundle\Entity\Profile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Meal
 *
 * @ORM\Table(name="meal")
 * @ORM\Entity(repositoryClass="MealRepository")
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
	 * @Assert\NotBlank()
	 * @Assert\Type(type="Mealz\MealBundle\Entity\Dish")
	 * @ORM\ManyToOne(targetEntity="Dish", inversedBy="meals")
	 * @ORM\JoinColumn(name="dish_id", referencedColumnName="id")
	 * @var Dish
	 */
	protected $dish;

	/**
	 * @Assert\NotBlank()
	 * @Assert\Type(type="float")
	 * @ORM\Column(type="decimal", precision=10, scale=4, nullable=FALSE)
	 * @var float
	 */
	protected $price;

	/**
	 * @Assert\NotBlank()
	 * @Assert\Type(type="DateTime")
	 * @ORM\Column(type="datetime", nullable=FALSE)
	 * @var \DateTime
	 */
	protected $dateTime;

	/**
	 * @var ArrayCollection
	 * @ORM\OneToMany(targetEntity="Participant", mappedBy="meal")
	 */
	public $participants;

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
	 * @param float $price
	 */
	public function setPrice($price)
	{
		$this->price = $price;
	}

	/**
	 * @return float
	 */
	public function getPrice()
	{
		return $this->price;
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

	/**
	 * get the participant object of the given profile if it is registered
	 *
	 * @param Profile $profile
	 * @return \Mealz\MealBundle\Entity\Participant|null
	 */
	public function getParticipant(Profile $profile) {
		foreach($this->participants as $participant) {
			/** @var Participant $participant */
			if(!$participant->isGuest() && $participant->getProfile() === $profile) {
				return $participant;
			}
		}
		return NULL;
	}

	/**
	 * get all guests that the given profile has invited
	 *
	 * @param Profile $profile
	 * @return Participant|null
	 */
	public function getGuestParticipants(Profile $profile) {
		$participants = array();
		foreach($this->participants as $participant) {
			/** @var Participant $participant */
			if($participant->isGuest() && $participant->getProfile() === $profile) {
				$participants[] = $participant;
			}
		}
		return $participants;
	}

	function __toString() {
		return $this->getDateTime()->format('Y-m-d H:i:s') . ' ' . $this->getDish();
	}


}
