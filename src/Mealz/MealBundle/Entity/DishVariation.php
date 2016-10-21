<?php

namespace Mealz\MealBundle\Entity;


use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


/**
 * Model representing a Dish variation.
 *
 * @ORM\Table(name="dish_variation")
 * @ORM\Entity(repositoryClass="DishVariationRepository")
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
}
