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

    /**
     * @Assert\Type(type="DateTime")
     * @ORM\Column(type="datetime", nullable=TRUE)
     * @var \DateTime $dateTime
     */
    private $lockParticipationDateTime;


    /**
     * Constructor
     * Day constructor.
     */
    public function __construct()
    {
        $this->meals = new ArrayCollection();
        $this->ensureLockParticipationDateTimeIsSet();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return ArrayCollection|Meal[]
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

    /**
     * add a Meal
     * @param Meal $meal
     */
    public function addMeal(Meal $meal)
    {
        $meal->setDay($this);
        $this->meals->add($meal);
    }

    /**
     * to String
     * @return string
     */
    public function __toString()
    {
        return $this->dateTime->format('l');
    }

    /**
     * @return \DateTime
     */
    public function getLockParticipationDateTime()
    {
        $this->ensureLockParticipationDateTimeIsSet();
        return $this->lockParticipationDateTime;
    }

    /**
     * @param \DateTime $lockParticipationDateTime
     */
    public function setLockParticipationDateTime($lockParticipationDateTime)
    {
        $this->lockParticipationDateTime = $lockParticipationDateTime;
    }

    /**
     * Initialize lockParticipationDateTime or set it to an decent value if NULL
     */
    private function ensureLockParticipationDateTimeIsSet(){
        if(!is_null($this->getId())){
            if (is_null($this->lockParticipationDateTime) == TRUE) {
                $_initial = clone $this->dateTime;
                $_initial->modify($GLOBALS['kernel']->getContainer()->getParameter('mealz.lock_toggle_participation_at'));
                $this->lockParticipationDateTime = $_initial;
            }
        }
    }
}