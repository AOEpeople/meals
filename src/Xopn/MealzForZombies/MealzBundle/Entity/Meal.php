<?php

namespace Xopn\MealzForZombies\MealzBundle\Entity;

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
     * @param \Xopn\MealzForZombies\MealzBundle\Entity\Dish $dish
     */
    public function setDish($dish)
    {
        $this->dish = $dish;
    }

    /**
     * @return \Xopn\MealzForZombies\MealzBundle\Entity\Dish
     */
    public function getDish()
    {
        return $this->dish;
    }

}
