<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use App\Mealz\MealBundle\Validator\Constraints as MealBundleAssert;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JsonSerializable;

/**
 * @ORM\Entity
 * @MealBundleAssert\DishConstraint()
 * @ORM\Table(name="meal")
 */
class Meal implements JsonSerializable
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="Dish", cascade={"refresh"}, fetch="EAGER")
     * @ORM\JoinColumn(name="dish_id", referencedColumnName="id")
     */
    private Dish $dish;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="decimal", precision=10, scale=4, nullable=FALSE)
     */
    private float $price = 0.0;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="integer", nullable=FALSE, name="participation_limit")
     */
    private int $participationLimit = 0;

    /**
     * @ORM\ManyToOne(targetEntity="Day", inversedBy="meals")
     * @ORM\JoinColumn(name="day", referencedColumnName="id")
     */
    private Day $day;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="DateTime")
     * @ORM\Column(type="datetime", nullable=FALSE)
     */
    private DateTime $dateTime;

    /**
     * @ORM\OneToMany(targetEntity="Participant", mappedBy="meal")
     *
     * @psalm-var Collection<int, Participant>
     */
    public ?Collection $participants = null;

    public function __construct(Dish $dish, Day $day)
    {
        $this->participants = new ArrayCollection();
        $this->dish = $dish;
        $this->day = $day;
        $this->dateTime = clone $day->getDateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setPrice(float $price): void
    {
        $this->price = $price;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setParticipationLimit(int $participationLimit): void
    {
        $this->participationLimit = $participationLimit;
    }

    public function getParticipationLimit(): int
    {
        return $this->participationLimit;
    }

    public function setDish(Dish $dish): void
    {
        $this->dish = $dish;
    }

    public function getDish(): Dish
    {
        return $this->dish;
    }

    public function getParticipants(): ArrayCollection
    {
        if (null === $this->participants) {
            $this->participants = new ArrayCollection();
        }

        return new ArrayCollection($this->participants->toArray());
    }

    public function getDay(): Day
    {
        return $this->day;
    }

    public function setDay(Day $day): void
    {
        $this->day = $day;
        $this->setDateTime($day->getDateTime());
    }

    public function setDateTime(DateTime $dateTime): void
    {
        $this->dateTime = $dateTime;
    }

    public function getDateTime(): DateTime
    {
        return $this->dateTime;
    }

    public function getLockDateTime(): DateTime
    {
        return $this->day->getLockParticipationDateTime();
    }

    public function isCombinedMeal(): bool
    {
        return $this->dish->isCombinedDish();
    }

    public function isLocked(): bool
    {
        return $this->getLockDateTime() <= (new DateTime('now'));
    }

    public function isOpen(): bool
    {
        return $this->dateTime > (new DateTime('now'));
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

    /**
     * @TODO don't load every participant object (raw sql query in repo?)
     */
    public function getTotalConfirmedParticipations(): int
    {
        $totalParticipation = 0;

        foreach ($this->getParticipants() as $participation) {
            /* @var Participant $participation */
            if ($participation->isConfirmed()) {
                ++$totalParticipation;
            }
        }

        return $totalParticipation;
    }

    public function hasReachedParticipationLimit(): bool
    {
        $participations = $this->getParticipants()->count();
        $limit = $this->getParticipationLimit();

        return 0 !== $limit && $participations >= $limit;
    }

    public function __toString()
    {
        return $this->getDateTime()->format('Y-m-d H:i:s') . ' ' . $this->getDish();
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'dish' => [
                'de' => $this->getDish()->getTitleDe(),
                'en' => $this->getDish()->getTitleEn(),
            ],
            'price' => $this->getPrice(),
            'participationLimit' => $this->getParticipationLimit(),
            'day' => $this->getDay(),
            'dateTime' => $this->getDateTime(),
            'participants' => $this->getParticipants(),
        ];
    }
}
