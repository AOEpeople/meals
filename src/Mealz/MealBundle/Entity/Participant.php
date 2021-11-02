<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Mealz\UserBundle\Entity\Profile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="participant")
 * @ORM\Entity(repositoryClass="ParticipantRepository")
 */
class Participant
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
     * @Assert\NotNull()
     * @Assert\Type(type="App\Mealz\MealBundle\Entity\Meal")
     * @ORM\ManyToOne(targetEntity="Meal",inversedBy="participants")
     * @ORM\JoinColumn(name="meal_id", referencedColumnName="id")
     * @var Meal
     */
    protected $meal;

    /**
     * @Assert\NotNull()
     * @ORM\ManyToOne(targetEntity="App\Mealz\MealBundle\Entity\Slot")
     * @ORM\JoinColumn(name="slot_id", referencedColumnName="id", nullable=true)
     */
    private ?Slot $slot = null;

    /**
     * @Assert\NotNull()
     * @Assert\Type(type="App\Mealz\UserBundle\Entity\Profile")
     * @ORM\ManyToOne(targetEntity="App\Mealz\UserBundle\Entity\Profile")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     * @var Profile
     */
    protected $profile;

    /**
     * @Assert\Length(min=3, max=2048)
     * @ORM\Column(type="string", length=2048, nullable=TRUE)
     */
    protected string $comment = '';

    /**
     * @Assert\Length(min=3, max=255)
     * @ORM\Column(type="string", length=255, nullable=TRUE)
     * @var string
     */
    protected $guestName;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     */
    protected bool $costAbsorbed = false;

    /**
     * @ORM\Column(type="integer", nullable=false, name="offeredAt")
     */
    protected int $offeredAt = 0;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default8": false})
     */
    protected bool $confirmed = false;


    public function __construct(Profile $profile, Meal $meal)
    {
        $this->profile = $profile;
        $this->meal = $meal;
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

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setMeal(Meal $meal): void
    {
        $this->meal = $meal;
    }

    /**
     * @return Meal
     */
    public function getMeal()
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

    /**
     * @return Profile
     */
    public function getProfile()
    {
        return $this->profile;
    }

    public function setComment(string $comment): void
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getComment(): string
    {
        return $this->comment;
    }

    /**
     * @param string $guestName
     */
    public function setGuestName($guestName)
    {
        $this->guestName = $guestName ?: null;
    }

    /**
     * @return bool
     */
    public function isGuest()
    {
        return $this->profile->isGuest();
    }

    /**
     * @return string
     */
    public function getGuestName()
    {
        return $this->guestName;
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
        return ($this->getOfferedAt() !== 0);
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
