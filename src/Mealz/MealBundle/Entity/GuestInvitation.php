<?php

namespace App\Mealz\MealBundle\Entity;

use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="guest_invitation")
 * @ORM\HasLifecycleCallbacks
 */
class GuestInvitation
{
    /**
     * @var string
     * @ORM\Column(name="id", type="string")
     * @ORM\Id
     */
    private $id;

    /**
     * @var DateTime
     * @ORM\Column(name="created_on", type="datetime")
     */
    private $createdOn;

    /**
     * @var Profile
     * @ORM\ManyToOne(targetEntity="App\Mealz\UserBundle\Entity\Profile")
     * @ORM\JoinColumn(name="host_id", referencedColumnName="id", nullable=FALSE, onDelete="NO ACTION")
     */
    private $host;

    /**
     * @var Day
     * @ORM\ManyToOne(targetEntity="Day")
     * @ORM\JoinColumn(name="meal_day_id", referencedColumnName="id", nullable=FALSE, onDelete="NO ACTION")
     */
    private $day;

    /**
     * Initializes class instance.
     */
    public function __construct(Profile $host, Day $day)
    {
        $this->host = $host;
        $this->day = $day;
    }

    /**
     * @param string $id
     */
    public function setId($id): GuestInvitation
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    public function setCreatedOn(DateTime $createdOn): GuestInvitation
    {
        $this->createdOn = $createdOn;

        return $this;
    }

    /**
     * @return DateTime
     */
    public function getCreatedOn()
    {
        return $this->createdOn;
    }

    /**
     * @return GuestInvitation
     */
    public function setHost(Profile $host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return Profile
     */
    public function getHost()
    {
        return $this->host;
    }

    public function setDay(Day $day): GuestInvitation
    {
        $this->day = $day;

        return $this;
    }

    /**
     * Get meal day.
     *
     * @return Day
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @ORM\PrePersist ()
     */
    public function beforeCreate(): void
    {
        $this->id = md5($this->host->getUsername() . $this->day->getId());
        $this->createdOn = new DateTime();
    }
}
