<?php

namespace App\Mealz\MealBundle\Entity;

use App\Mealz\MealBundle\Validator\Constraints as MealBundleAssert;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Meal.
 *
 * @MealBundleAssert\DishConstraint()
 * @ORM\Table(name="meal")
 * @ORM\Entity(repositoryClass="MealRepository")
 */
class Meal
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private ?int $id = null;

    /**
     * @ORM\ManyToOne(targetEntity="Dish", cascade={"refresh"}, fetch="EAGER")
     * @ORM\JoinColumn(name="dish_id", referencedColumnName="id")
     *
     * @var Dish
     */
    protected $dish;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="decimal", precision=10, scale=4, nullable=FALSE)
     *
     * @var float
     */
    protected $price;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="integer", nullable=FALSE, name="participation_limit")
     *
     * @var int
     */
    private int $participationLimit = 0;

    /**
     * @ORM\ManyToOne(targetEntity="Day", inversedBy="meals")
     * @ORM\JoinColumn(name="day", referencedColumnName="id")
     *
     * @var Day
     */
    protected $day;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="DateTime")
     * @ORM\Column(type="datetime", nullable=FALSE)
     *
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @ORM\OneToMany(targetEntity="Participant", mappedBy="meal")
     * @psalm-var Collection<int, Participant>
     */
    public ?Collection $participants = null;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param float $price
     */
    public function setPrice($price): void
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param int $participationLimit
     */
    public function setParticipationLimit($participationLimit): void
    {
        $this->participationLimit = $participationLimit;
    }

    /**
     * @return int
     */
    public function getParticipationLimit()
    {
        return $this->participationLimit;
    }

    /**
     * @param Dish $dish
     */
    public function setDish($dish): void
    {
        $this->dish = $dish;
    }

    /**
     * @return Dish
     */
    public function getDish()
    {
        return $this->dish;
    }

    public function getParticipants(): Collection
    {
        if (null === $this->participants) {
            $this->participants = new ArrayCollection();
        }

        return new ArrayCollection($this->participants->toArray());
    }

    /**
     * @return Day
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @param Day $day
     */
    public function setDay($day): void
    {
        $this->day = $day;
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

    /**
     * get the participant object of the given profile if it is registered.
     *
     * @return Participant|null
     */
    public function getParticipant(Profile $profile)
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
     * get all guests that the given profile has invited.
     *
     * @return Participant|null
     */
    public function getGuestParticipants(Profile $profile)
    {
        $participants = [];
        foreach ($this->participants as $participant) {
            /** @var Participant $participant */
            if ($participant->isGuest() && $participant->getProfile() === $profile) {
                $participants[] = $participant;
            }
        }

        return $participants;
    }

    /**
     * Return the number of total confirmed participations.
     *
     * @TODO don't load every participant object (raw sql query in repo?)
     *
     * @return int
     */
    public function getTotalConfirmedParticipations()
    {
        $totalParticipations = 0;

        foreach ($this->getParticipants() as $participation) {
            /* @var Participant $participation */
            if ($participation->isConfirmed()) {
                ++$totalParticipations;
            }
        }

        return $totalParticipations;
    }

    /**
     * Check if there are more or equal participation for this meal as its participation limit.
     */
    public function isParticipationLimitReached(): bool
    {
        $participationLimit = $this->getParticipationLimit();

        return 0 !== $participationLimit && $this->getParticipants()->count() >= $participationLimit;
    }

    public function __toString()
    {
        return $this->getDateTime()->format('Y-m-d H:i:s') . ' ' . $this->getDish();
    }
}
