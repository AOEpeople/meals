<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="day")
 */
class Day extends AbstractMessage implements JsonSerializable
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
     * @ORM\OneToOne(targetEntity="EventParticipation", mappedBy="day")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=true)
     */
    private ?EventParticipation $event = null;

    /**
     * @Assert\Type(type="DateTime")
     * @ORM\Column(type="datetime", nullable=TRUE)
     */
    private DateTime $lockParticipationDateTime;

    public function __construct()
    {
        $this->dateTime = new DateTime();
        $this->week = $this->getDefaultWeek($this->dateTime);
        $this->lockParticipationDateTime = $this->dateTime;
        $this->meals = new MealCollection();
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

    public function getEvent(): ?EventParticipation
    {
        return $this->event;
    }

    public function setEvent(?EventParticipation $event): void
    {
        $this->event = $event;
    }

    public function getMeals(): MealCollection
    {
        if (false === ($this->meals instanceof Collection)) {
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

    private function getDefaultWeek(DateTime $date): Week
    {
        $year = (int) $date->format('Y');
        $calWeek = (int) $date->format('W');

        $week = new Week();
        $week->setYear($year);
        $week->setCalendarWeek($calWeek);
        $week->setDays(new ArrayCollection([$this]));

        return $week;
    }

    public function jsonSerialize(): array
    {
        $meals = [];

        foreach ($this->getMeals() as $meal) {
            // if (true === $meal->isCombinedMeal()) {
            //     continue;
            // }
            $parent = $meal->getDish()->getParent();
            if (null !== $parent) {
                $meals[$parent->getId()][] = $meal->jsonSerialize();
            } else {
                $meals[$meal->getDish()->getId()][] = $meal->jsonSerialize();
            }
        }

        return [
            'dateTime' => $this->getDateTime(),
            'lockParticipationDateTime' => $this->getLockParticipationDateTime(),
            'week' => $this->getWeek()->getId(),
            'meals' => $meals,
            'enabled' => $this->isEnabled(),
        ];
    }
}
