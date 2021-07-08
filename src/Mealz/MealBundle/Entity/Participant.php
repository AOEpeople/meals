<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mealz\UserBundle\Entity\Profile;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Dish
 *
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
     * @Assert\Type(type="Mealz\MealBundle\Entity\Meal")
     * @ORM\ManyToOne(targetEntity="Meal",inversedBy="participants")
     * @ORM\JoinColumn(name="meal_id", referencedColumnName="id")
     * @var Meal
     */
    protected $meal;

    /**
     * @Assert\NotNull()
     * @Assert\Type(type="Mealz\UserBundle\Entity\Profile")
     * @ORM\ManyToOne(targetEntity="Mealz\UserBundle\Entity\Profile")
     * @ORM\JoinColumn(name="profile_id", referencedColumnName="id")
     * @var Profile
     */
    protected $profile;

    /**
     * @Assert\Length(min=3, max=2048)
     * @ORM\Column(type="string", length=2048, nullable=TRUE)
     * @var string
     */
    protected $comment;

    /**
     * @Assert\Length(min=3, max=255)
     * @ORM\Column(type="string", length=255, nullable=TRUE)
     * @var string
     */
    protected $guestName;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default": false})
     * @var boolean
     */
    protected $costAbsorbed = false;

    /**
     * @ORM\Column(type="integer", nullable=false, name="offeredAt")
     * @var integer
     */
    protected $offeredAt = 0;

    /**
     * @ORM\Column(type="boolean", nullable=false, options={"default8": false})
     * @var boolean
     */
    protected $confirmed = false;

    /**
     * @return boolean
     */
    public function isConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * @param boolean $isConfirmed
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;
    }

    /**
     * @return boolean
     */
    public function isCostAbsorbed()
    {
        return $this->costAbsorbed;
    }

    /**
     * @param boolean $costAbsorbed
     */
    public function setCostAbsorbed($costAbsorbed)
    {
        $this->costAbsorbed = $costAbsorbed;
    }

    /**
     * @return bool
     */
    public function isAccountable()
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

    /**
     * @param Meal $meal
     */
    public function setMeal($meal)
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

    /**
     * @param Profile $profile
     */
    public function setProfile($profile)
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

    /**
     * @param string $comment
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
    }

    /**
     * @return string
     */
    public function getComment()
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

    /**
     * @param $offeredAt
     */
    public function setOfferedAt($offeredAt)
    {
        $this->offeredAt = $offeredAt;
    }

    /**
     * @return integer
     */
    public function getOfferedAt()
    {
        return $this->offeredAt;
    }

    /**
     * @return bool
     */
    public function isPending()
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
