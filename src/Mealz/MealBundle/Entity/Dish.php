<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

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
	 * @ORM\Column(type="string", length=255, nullable=FALSE)
	 * @var string
	 */
	protected $title;

	/**
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

	public function __toString() {
		return $this->getTitle();
	}

}
