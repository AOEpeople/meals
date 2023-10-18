<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="participant")
 */
class Participant
{
    /**
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @Assert\NotNull()
     * @ORM\ManyToOne(targetEntity="App\Mealz\MealBundle\Entity\Event", inversedBy="participants")
     * @ORM\JoinColumn(name="event_id", referencedColumnName="id", nullable=true)
     */
    private ?Event $event = null;

    /**
     * @Assert\NotNull()
     * @Assert\Type(type="App\Mealz\MealBundle\Entity\Meal")
     * @ORM\ManyToOne(targetEntity="Meal",inversedBy="participants")
     * @ORM\JoinColumn(name="meal_id", referencedColumnName="id")
     */
    private ?Meal $meal;

    /**
     * @Assert\NotNull()
     * @ORM\ManyToOne(targetEntity="App\Mealz\MealBundle\Entity\Slot", inversedBy="participants")
     * @ORM\JoinColumn(name="slot_id", referencedColumnName="id", nullable=true)
     */
    private ?Slot $slot = null;

    /**
     * @ORM\ManyToMany(targetEntity="Dish")
     *
     * @var Collection<int, Dish>|null
     */
    private ?Collection $combinedDishes;

    /**
     * @Assert\NotNull()
     * @Assert\Type(type="App\Mealz\UserBundle\Entity\Profile")
     * @ORM\ManyToOne(targetEntity="App\Mealz\UserBundle\Entity\Profile")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     */
    private Profile $profile;

    /**
     * @Assert\Length(min=3, max=2048)
     * @ORM\Column(type="string", length=2048, nullable=TRUE)
     */
    private ?string $comment = null;

    /**
     * @Assert\Length(min=3, max=255)
     * @ORM\Column(type="string", length=255, nullable=TRUE)
     */
    private ?string $guestName = null;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     */
    private bool $costAbsorbed = false;

    /**
     * @ORM\Column(type="integer", nullable=false, name="offeredAt")
     */
    private int $offeredAt = 0;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     */
    private bool $confirmed = false;

    public function __construct(Profile $profile, ?Meal $meal, ?Event $event = null)
    {
        $this->profile = $profile;
        $this->meal = $meal;
        $this->event = $event;
        $this->combinedDishes = new DishCollection();
    }

    public function isConfirmed(): bool
    {
        return $this->confirmed;
    }

    public function setConfirmed(bool $confirmed): void
    {
        $this->confirmed = $confirmed;
    }

    public function isCostAbsorbed(): bool
    {
        return $this->costAbsorbed;
    }

    public function setCostAbsorbed(bool $costAbsorbed): void
    {
        $this->costAbsorbed = $costAbsorbed;
    }

    public function isAccountable(): bool
    {
        return !$this->isCostAbsorbed();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEvent(): ?Event
    {
        return $this->event;
    }

    public function setEvent(Event $event): void
    {
        $this->event = $event;
    }

    public function setMeal(Meal $meal): void
    {
        $this->meal = $meal;
    }

    public function getMeal(): ?Meal
    {
        return $this->meal;
    }

    public function getSlot(): ?Slot
    {
        return $this->slot;
    }

    public function setSlot(Slot $slot): void
    {
        $this->slot = $slot;
    }

    public function setProfile(Profile $profile): void
    {
        $this->profile = $profile;
    }

    public function getProfile(): Profile
    {
        return $this->profile;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    public function getComment(): string
    {
        return $this->comment ?? '';
    }

    public function setGuestName(string $guestName): void
    {
        $this->guestName = $guestName;
    }

    public function getGuestName(): string
    {
        return $this->guestName ?? '';
    }

    public function isGuest(): bool
    {
        return $this->profile->isGuest();
    }

    public function setOfferedAt(int $offeredAt): void
    {
        $this->offeredAt = $offeredAt;
    }

    public function getOfferedAt(): int
    {
        return $this->offeredAt;
    }

    public function isPending(): bool
    {
        return 0 !== $this->getOfferedAt();
    }

    public function getCombinedDishes(): DishCollection
    {
        if (null === $this->combinedDishes) {
            return new DishCollection();
        }

        return new DishCollection($this->combinedDishes->toArray());
    }

    /**
     * @param Dish[] $dishes
     */
    public function setCombinedDishes(?array $dishes): void
    {
        if (null === $dishes) {
            if (null !== $this->combinedDishes) {
                $this->combinedDishes->clear();
            }
        } else {
            $this->combinedDishes = new DishCollection($dishes);
        }
    }

    public function __toString()
    {
        return $this->getMeal() . ' ' . $this->getProfile();
    }

    public function __clone()
    {
        $this->id = null;
    }
}
