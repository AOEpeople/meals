<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Dish
 *
 * @ORM\Table(name="dish")
 * @ORM\Entity
 */
class Dish
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
	 * @Assert\Length(min=8, max=255)
	 * @ORM\Column(type="string", length=255, nullable=FALSE)
	 * @var string
	 */
	protected $title;

	/**
	 * @Assert\Length(max=4096)
	 * @ORM\Column(type="text", nullable=TRUE)
	 * @var null|string
	 */
	protected $description = NULL;

	/**
	 * @ORM\Column(type="decimal", precision=10, scale=4, nullable=TRUE)
	 * @var null|float
	 */
	protected $price = NULL;

	/**
	 * @ORM\Column(type="boolean", nullable=FALSE)
	 * @var bool
	 */
	protected $enabled = TRUE;

	/**
	 * @ORM\OneToMany(targetEntity="Meal", mappedBy="dish")
	 * @var ArrayCollection
	 */
	protected $meals;

	public function __construct() {
		$this->meals = new ArrayCollection();
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
	 * @param null|string $description
	 */
	public function setDescription($description)
	{
		$this->description = $description;
	}

	/**
	 * @return null|string
	 */
	public function getDescription()
	{
		return $this->description;
	}

	/**
	 * @param float|null $price
	 */
	public function setPrice($price)
	{
		$this->price = $price;
	}

	/**
	 * @return float|null
	 */
	public function getPrice()
	{
		return $this->price;
	}

	/**
	 * @param string $title
	 */
	public function setTitle($title)
	{
		$this->title = $title;
	}

	/**
	 * @return string
	 */
	public function getTitle()
	{
		return $this->title;
	}

	/**
	 * @param boolean $enabled
	 */
	public function setEnabled($enabled)
	{
		$this->enabled = $enabled;
	}

	/**
	 * @return boolean
	 */
	public function isEnabled()
	{
		return $this->enabled;
	}

	/**
	 * @return ArrayCollection
	 */
	public function getMeals() {
		return $this->meals;
	}

	public function __toString() {
		return $this->getTitle();
	}

}
