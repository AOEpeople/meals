<?php

namespace App\Mealz\MealBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use App\Mealz\UserBundle\Entity\Profile;
use Symfony\Component\Validator\Constraints as Assert;
use App\Mealz\MealBundle\Validator\Constraints as MealBundleAssert;

/**
 * Meal
 *
 * @MealBundleAssert\DishConstraint()
 * @ORM\Table(name="meal")
 * @ORM\Entity(repositoryClass="MealRepository")
 */
class Meal
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @SuppressWarnings(PHPMD.ShortVariable)
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Dish", cascade={"refresh"}, fetch="EAGER")
     * @ORM\JoinColumn(name="dish_id", referencedColumnName="id")
     * @var Dish
     */
    protected $dish;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="decimal", precision=10, scale=4, nullable=FALSE)
     * @var float
     */
    protected $price;

    /**
     * @Assert\NotBlank()
     * @ORM\Column(type="integer", nullable=FALSE, name="participation_limit")
     * @var integer
     */
    protected $participationLimit;

    /**
     * @ORM\ManyToOne(targetEntity="Day", inversedBy="meals")
     * @ORM\JoinColumn(name="day", referencedColumnName="id")
     * @var Day
     */
    protected $day;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(type="DateTime")
     * @ORM\Column(type="datetime", nullable=FALSE)
     * @var DateTime
     */
    protected $dateTime;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Participant", mappedBy="meal")
     */
    public $participants;

    public function __construct()
    {
        $this->participants = new ArrayCollection();
        $this->participationLimit = 0;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
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
     * @param integer $participationLimit
     */
    public function setParticipationLimit($participationLimit)
    {
        $this->participationLimit = $participationLimit;
    }

    /**
     * @return integer
     */
    public function getParticipationLimit()
    {
        return $this->participationLimit;
    }

    /**
     * @param Dish $dish
     */
    public function setDish($dish)
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

    /**
     * @return ArrayCollection
     */
    public function getParticipants()
    {
        return $this->participants;
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
    public function setDay($day)
    {
        $this->day = $day;
    }

    /**
     * @param DateTime $dateTime
     */
    public function setDateTime($dateTime)
    {
        $this->dateTime = $dateTime;
    }

    /**
     * @return DateTime
     */
    public function getDateTime()
    {
        return $this->dateTime;
    }

    /**
     * get the participant object of the given profile if it is registered
     *
     * @param Profile $profile
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
     * get all guests that the given profile has invited
     *
     * @param Profile $profile
     * @return Participant|null
     */
    public function getGuestParticipants(Profile $profile)
    {
        $participants = array();
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
                $totalParticipations += 1;
            }
        }

        return $totalParticipations;
    }

    /**
     * Check if there are more or equal participation for this meal as its participation limit.
     *
     * @return bool
     */
    public function isParticipationLimitReached()
    {
        return ($this->getParticipationLimit() != 0 && $this->getParticipants()->count() >= $this->getParticipationLimit());
    }

    public function __toString()
    {
        return $this->getDateTime()->format('Y-m-d H:i:s').' '.$this->getDish();
    }
}
