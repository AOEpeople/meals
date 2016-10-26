<?php

namespace Mealz\MealBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Model representing a Dish variation.
 *
 * @ORM\Table(name="dish_variation")
 * @ORM\Entity(repositoryClass="DishVariationRepository")
 *
 * @author Chetan Thapliyal <chetan.thapliyal@aoe.com>
 */
class DishVariation
{
	/**
	 * @var integer
	 *
	 * @ORM\Column(name="id", type="integer")
	 * @ORM\Id
	 * @ORM\GeneratedValue(strategy="AUTO")
	 */
	protected $id;

	/**
	 * @ORM\ManyToOne(targetEntity="Dish", inversedBy="variations")
	 * @ORM\JoinColumn(name="dish_id", referencedColumnName="id", nullable=FALSE, onDelete="CASCADE")
	 * @var Dish
	 */
	protected $dish = NULL;

	/**
	 * @Assert\Length(max=2048)
	 * @ORM\Column(type="string", length=4096, nullable=FALSE)
	 * @var string
	 */
	protected $description_de;

	/**
	 * @Assert\Length(max=2048)
	 * @ORM\Column(type="string", length=4096, nullable=FALSE)
	 * @var string
	 */
	protected $description_en;

	/**
	 * @ORM\Column(type="boolean", nullable=FALSE)
	 * @var bool
	 */
	protected $enabled = TRUE;

	/**
	 * @var string
	 */
	protected $currentLocale = 'en';

	/**
	 * @return int
	 */
	public function getId() {
		return $this->id;
	}

	/**
	 * @param int $id
	 */
	public function setId($id) {
		$this->id = $id;
	}

	/**
	 * @return Dish
	 */
	public function getDish() {
		return $this->dish;
	}

	/**
	 * @param Dish $dish
	 */
	public function setDish($dish) {
		$this->dish = $dish;
	}

	/**
	 * @return NULL|string
	 */
	public function getDescription()
	{
		if($this->currentLocale == 'de' && $this->description_de) {
			return $this->getDescriptionDe();
		} else {
			return $this->getDescriptionEn();
		}
	}

	/**
	 * @return string
	 */
	public function getDescriptionDe() {
		return $this->description_de;
	}

	/**
	 * @param string $description_de
	 */
	public function setDescriptionDe($description_de) {
		$this->description_de = $description_de;
	}

	/**
	 * @return string
	 */
	public function getDescriptionEn() {
		return $this->description_en;
	}

	/**
	 * @param string $description_en
	 */
	public function setDescriptionEn($description_en) {
		$this->description_en = $description_en;
	}

	/**
	 * @return boolean
	 */
	public function isEnabled() {
		return $this->enabled;
	}

	/**
	 * @param boolean $enabled
	 */
	public function setEnabled($enabled) {
		$this->enabled = $enabled;
	}

	/**
	 * @return string
	 */
	public function getCurrentLocale() {
		return $this->currentLocale;
	}

	/**
	 * @param string $currentLocale
	 */
	public function setCurrentLocale($currentLocale) {
		$this->currentLocale = $currentLocale;
	}
}
