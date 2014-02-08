<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mealz\UserBundle\Entity\Profile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Dish
 *
 * @ORM\Table(name="participant")
 * @ORM\Entity(repositoryClass="ParticipantRepository")
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
	 * @Assert\Type(type="Mealz\UserBundle\Entity\Profile")
	 * @ORM\ManyToOne(targetEntity="Mealz\UserBundle\Entity\Profile")
	 * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
	 * @var Profile
	 */
	protected $profile;

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
	 * @param \Mealz\UserBundle\Entity\Profile $profile
	 */
	public function setProfile($profile)
	{
		$this->profile = $profile;
	}

	/**
	 * @return \Mealz\UserBundle\Entity\Profile
	 */
	public function getProfile()
	{
		return $this->profile;
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
		return $this->getMeal() . ' ' . $this->getProfile();
	}

	public function __clone() {
		$this->id = NULL;
	}


}
