<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Day
 *
 * @ORM\Table(name="day")
 * @ORM\Entity(repositoryClass="DayRepository")
 */
class Day extends AbstractMessage
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer $id
     */
    private $id;

    /**
     * @Assert\Type(type="DateTime")
     * @ORM\Column(type="datetime", nullable=FALSE)
     * @var \DateTime $dateTime
     */
    private $dateTime;

    /**
     * @ORM\ManyToOne(targetEntity="Week", inversedBy="days")
     * @ORM\JoinColumn(name="week_id", referencedColumnName="id")
     * @var Week $week
     */
    private $week;

    /**
     * @ORM\OneToMany(targetEntity="Meal", mappedBy="day", cascade={"all"})
     * @var ArrayCollection $meals
     */
    private $meals;

    public function __construct()
    {
        $this->meals = new ArrayCollection();
    }

    /**
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * @param \DateTime $dateTime
     */
    public function setDateTime($dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @return Week
     */
    public function getWeek()
    {
        return $this->week;
    }

    /**
     * @param Week $week
     */
    public function setWeek($week)
    {
        $this->week = $week;
    }

    /**
     * @return ArrayCollection
     */
    public function getMeals()
    {
        return $this->meals;
    }

    /**
     * @param ArrayCollection $meals
     */
    public function setMeals($meals)
    {
        $this->meals = $meals;
    }

    public function __toString()
    {
        return $this->dateTime->format('l');
    }
}