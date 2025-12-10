<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use App\Mealz\AccountingBundle\Entity\Price;
use App\Mealz\MealBundle\Validator\Constraints as MealBundleAssert;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JsonSerializable;
use Override;
use Stringable;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @MealBundleAssert\DishConstraint()
 */
#[ORM\Entity]
#[ORM\Table(name: 'meal')]
class Meal implements Stringable, JsonSerializable
{
    #[ORM\Id]
    #[ORM\Column(name: 'id', type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: Dish::class, cascade: ['refresh'], fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'dish_id', referencedColumnName: 'id')]
    private Dish $dish;

    #[Assert\NotBlank]
    #[ORM\ManyToOne(targetEntity: Price::class, cascade: ['refresh'], fetch: 'EAGER')]
    #[ORM\JoinColumn(name: 'price_id', referencedColumnName: 'year')]
    private Price $price;

    #[Assert\NotBlank]
    #[ORM\Column(name: 'participation_limit', type: 'integer', nullable: false)]
    private int $participationLimit = 0;

    #[ORM\ManyToOne(targetEntity: Day::class, inversedBy: 'meals')]
    #[ORM\JoinColumn(name: 'day', referencedColumnName: 'id')]
    private Day $day;

    #[Assert\NotBlank]
    #[ORM\Column(type: 'datetime', nullable: false)]
    private DateTime $dateTime;

    /**
     * @psalm-var Collection<int, Participant>
     */
    #[ORM\OneToMany(mappedBy: 'meal', targetEntity: Participant::class)]
    public ?Collection $participants = null;

    public function __construct(Dish $dish, Price $price, Day $day)
    {
        $this->participants = new ArrayCollection();
        $this->dish = $dish;
        $this->price = $price;
        $this->day = $day;
        $this->dateTime = clone $day->getDateTime();
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPrice(): Price
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

    /**
     * @psalm-return ArrayCollection<int, Participant>
     */
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

    public function hasParticipations(): bool
    {
        return $this->getParticipants()->count() > 0;
    }

    /**
     * get the participant object of the given profile if it is registered.
     */
    public function getParticipant(Profile $profile): ?Participant
    {
        foreach ($this->participants as $participant) {
            /** @var Participant $participant */
            if (false === $participant->isGuest() && $participant->getProfile() === $profile) {
                return $participant;
            }
        }

        return null;
    }

    public function isParticipant(Participant $participantToCheck): bool
    {
        /** @var Participant $participant */
        foreach ($this->participants as $participant) {
            if ($participant === $participantToCheck) {
                return true;
            }
        }

        return false;
    }

    public function hasReachedParticipationLimit(): bool
    {
        $participations = $this->getParticipants()->count();
        $limit = $this->getParticipationLimit();

        return 0 !== $limit && $participations >= $limit;
    }

    #[Override]
    public function __toString()
    {
        return $this->getDateTime()->format('Y-m-d H:i:s') . ' ' . $this->getDish();
    }

    /**
     * @return (DateTime|int|string|null)[]
     *
     * @psalm-return array{id: int|null, dish: null|string, participationLimit: int, day: int|null, dateTime: DateTime, lockTime: DateTime}
     */
    #[Override]
    public function jsonSerialize(): array
    {
        return [
            'id' => $this->getId(),
            'dish' => $this->getDish()->getSlug(),
            'participationLimit' => $this->getParticipationLimit(),
            'day' => $this->getDay()->getId(),
            'dateTime' => $this->getDateTime(),
            'lockTime' => $this->getLockDateTime(),
        ];
    }
}
