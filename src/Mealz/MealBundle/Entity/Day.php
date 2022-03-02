<?php

namespace App\Mealz\MealBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="day")
 * @ORM\Entity(repositoryClass="DayRepository")
 */
class Day extends AbstractMessage
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    /**
     * @Assert\Type(type="DateTime")
     * @ORM\Column(type="datetime", nullable=FALSE)
     *
     * @var DateTime
     */
    private $dateTime;

    /**
     * @ORM\ManyToOne(targetEntity="Week", inversedBy="days")
     * @ORM\JoinColumn(name="week_id", referencedColumnName="id")
     *
     * @var Week
     */
    private $week;

    /**
     * @ORM\OneToMany(targetEntity="Meal", mappedBy="day", cascade={"all"})
     *
     * @var Collection<int, Meal>
     */
    private Collection $meals;

    /**
     * @Assert\Type(type="DateTime")
     * @ORM\Column(type="datetime", nullable=TRUE)
     *
     * @var DateTime
     */
    private $lockParticipationDateTime;

    /**
     * Constructor
     * Day constructor.
     */
    public function __construct()
    {
        $this->meals = new MealCollection();
        $this->dateTime = new DateTime();
        $this->lockParticipationDateTime = $this->dateTime;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     *
     * @SuppressWarnings (PHPMD.ShortVariable)
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * @param DateTime $dateTime
     */
    public function setDateTime($dateTime): void
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
    public function setWeek($week): void
    {
        $this->week = $week;
    }

    public function getMeals(): MealCollection
    {
        if (!($this->meals instanceof Collection)) {
            $this->meals = new MealCollection();
        }

        return new MealCollection($this->meals->toArray());
    }

    /**
     * @param MealCollection $meals
     */
    public function setMeals($meals): void
    {
        $this->meals = $meals;
    }

    public function addMeal(Meal $meal): void
    {
        $meal->setDay($this);
        $this->meals->add($meal);
    }

    public function removeMeal(Meal $meal): void
    {
        $this->meals->removeElement($meal);
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->dateTime->format('l');
    }

    /**
     * @return DateTime
     */
    public function getLockParticipationDateTime()
    {
        return $this->lockParticipationDateTime;
    }

    /**
     * @param DateTime $lockDateTime
     */
    public function setLockParticipationDateTime($lockDateTime): void
    {
        $this->lockParticipationDateTime = $lockDateTime;
    }
}
