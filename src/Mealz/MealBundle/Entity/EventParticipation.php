<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Defines an event participation.
 */
#[ORM\Entity]
#[ORM\Table(name: 'event_participation')]
class EventParticipation
{
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\OneToOne(inversedBy: 'event', targetEntity: Day::class)]
    #[ORM\JoinColumn(name: 'day', referencedColumnName: 'id')]
    private Day $day;

    #[ORM\ManyToOne(targetEntity: Event::class, cascade: ["persist"])]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
    private Event $event;

    #[ORM\OneToMany(mappedBy: 'event', targetEntity: Participant::class)]
    public ?Collection $participants = null;

    public function __construct(Day $day, Event $event, ?Collection $participants = null)
    {
        $this->day = $day;
        $this->event = $event;
        $this->participants = $participants;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDay(): Day
    {
        return $this->day;
    }

    public function setDay(Day $day): void
    {
        $this->day = $day;
    }

    public function getEvent(): Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    /**
     * get the participant object of the given profile if it is registered.
     */
    public function getParticipant(Profile $profile): ?Participant
    {
        foreach ($this->participants as $participant) {
            /** @var Participant $participant */
            if (!$participant->isGuest() && $participant->getProfile() === $profile) {
                return $participant;
            }
        }

        return null;
    }

    public function getParticipants(): ArrayCollection
    {
        if (null === $this->participants) {
            $this->participants = new ArrayCollection();
        }

        return new ArrayCollection($this->participants->toArray());
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'event' => $this->getEvent(),
            'day' => $this->getDay()->getId(),
            'participants' => $this->getParticipants(),
        ];
    }
}
