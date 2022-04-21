<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="day")
 * @ORM\Entity(repositoryClass="App\Mealz\MealBundle\Entity\DayRepository")
 */
class Day extends AbstractMessage
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @Assert\Type(type="DateTime")
     * @ORM\Column(type="datetime", nullable=FALSE)
     */
    private DateTime $dateTime;

    /**
     * @ORM\ManyToOne(targetEntity="Week", inversedBy="days")
     * @ORM\JoinColumn(name="week_id", referencedColumnName="id")
     */
    private Week $week;

    /**
     * @ORM\OneToMany(targetEntity="Meal", mappedBy="day", cascade={"all"})
     *
     * @var Collection<int, Meal>
     */
    private Collection $meals;

    /**
     * @Assert\Type(type="DateTime")
     * @ORM\Column(type="datetime", nullable=TRUE)
     */
    private DateTime $lockParticipationDateTime;

    public function __construct()
    {
        $this->week = new Week();
        $this->meals = new MealCollection();
        $this->dateTime = new DateTime();
        $this->lockParticipationDateTime = $this->dateTime;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getDateTime(): DateTime
    {
        return $this->dateTime;
    }

    public function setDateTime(DateTime $dateTime): void
    {
        $this->dateTime = $dateTime;
    }

    public function getWeek(): Week
    {
        return $this->week;
    }

    public function setWeek(Week $week): void
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

    public function setMeals(MealCollection $meals): void
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

    public function getLockParticipationDateTime(): DateTime
    {
        return $this->lockParticipationDateTime;
    }

    public function setLockParticipationDateTime(DateTime $lockDateTime): void
    {
        $this->lockParticipationDateTime = $lockDateTime;
    }

    public function __toString(): string
    {
        return $this->dateTime->format('l');
    }
}
