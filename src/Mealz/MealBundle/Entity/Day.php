<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;

#[ORM\Entity]
#[ORM\Table(name: 'day')]
class Day extends AbstractMessage implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\Column(type: 'datetime', nullable: false)]
    private DateTime $dateTime;

    #[ORM\ManyToOne(targetEntity: Week::class, inversedBy: 'days')]
    #[ORM\JoinColumn(name: 'week_id', referencedColumnName: 'id')]
    private Week $week;

    /**
     * @var Collection<int, Meal>
     */
    #[ORM\OneToMany(mappedBy: 'day', targetEntity: Meal::class, cascade: ['all'])]
    private Collection $meals;

    /**
     * @var Collection<int, EventParticipation>
     */
    #[ORM\OneToMany(mappedBy: 'day', targetEntity: EventParticipation::class, cascade: ['all'])]
    private Collection $event_participations;

    #[ORM\Column(name: 'lockParticipationDateTime', type: 'datetime', nullable: true)]
    private DateTime $lockParticipationOn;

    public function __construct()
    {
        $this->dateTime = new DateTime();
        $this->week = $this->getDefaultWeek($this->dateTime);
        $this->lockParticipationOn = $this->dateTime;
        $this->meals = new MealCollection();
        $this->event_participations = new EventCollection();
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

    public function getEvents(): ?EventCollection
    {
        if (false === ($this->event_participations instanceof Collection)) {
            $this->event_participations = new EventCollection();
        }

        return new EventCollection($this->event_participations->toArray());
    }

    public function getEvent(int $id): ?EventParticipation
    {
        foreach ($this->getEvents() as $event) {
            if ($event->getId() === $id) {
                return $event;
            }
        }

        return null;
    }

    public function addEvent(EventParticipation $event)
    {
        $event->setDay($this);
        $this->event_participations->add($event);
    }

    public function removeEvent(EventParticipation $event)
    {
        if ($this->event_participations->contains($event)) {
            $this->event_participations->removeElement($event);
        }
    }

    public function removeEvents()
    {
        $this->event_participations->clear();
    }

    public function setEvents(EventCollection $events): void
    {
        $this->event_participations = $events;
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
        return $this->lockParticipationOn;
    }

    public function setLockParticipationDateTime(DateTime $lockDateTime): void
    {
        $this->lockParticipationOn = $lockDateTime;
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
        /** @psalm-suppress InvalidArgument */
        // TODO: check if future versions of psalm can deal with it
        $week->setDays(new ArrayCollection([$this]));

        return $week;
    }

    /**
     * @return (DateTime|array[][]|bool|int|null)[]
     *
     * @psalm-return array{dateTime: DateTime, dayId: int|null, enabled: bool, events: array<int, array<array-key, mixed>>, lockParticipationDateTime: DateTime, meals: array<''|int, non-empty-list<array{dateTime: DateTime, day: int|null, dish: null|string, id: int|null, lockTime: DateTime, participationLimit: int}>>, week: int|null}
     */
    public function jsonSerialize(): array
    {
        $meals = [];
        $events = [];

        foreach ($this->getMeals() as $meal) {
            $parent = $meal->getDish()->getParent();
            if (null !== $parent) {
                $meals[$parent->getId()][] = $meal->jsonSerialize();
            } else {
                $meals[$meal->getDish()->getId()][] = $meal->jsonSerialize();
            }
        }
        foreach ($this->getEvents() as $event) {
            if (null !== $event && $event instanceof EventParticipation) {
                $eventId = $event->getId();
                if (isset($eventId)) {
                    $events[$eventId] = $event->jsonSerialize();
                }
            }
        }

        return [
            'dateTime' => $this->getDateTime(),
            'lockParticipationDateTime' => $this->getLockParticipationDateTime(),
            'week' => $this->getWeek()->getId(),
            'meals' => $meals,
            'events' => $events,
            'enabled' => $this->isEnabled(),
            'dayId' => $this->getId(),
        ];
    }
}
