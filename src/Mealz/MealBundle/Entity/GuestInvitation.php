<?php

namespace App\Mealz\MealBundle\Entity;

use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'guest_invitation')]
#[ORM\HasLifecycleCallbacks]
class GuestInvitation
{
    #[ORM\Id]
    #[ORM\Column(type: 'string')]
    private string $id;

    #[ORM\Column(name: 'created_on', type: 'datetime')]
    private DateTime $createdOn;

    #[ORM\ManyToOne(targetEntity: Profile::class)]
    #[ORM\JoinColumn(name: 'host_id', referencedColumnName: 'id', nullable: false, onDelete: 'NO ACTION')]
    private Profile $host;

    #[ORM\ManyToOne(targetEntity: Day::class)]
    #[ORM\JoinColumn(name: 'meal_day_id', referencedColumnName: 'id', nullable: false, onDelete: 'NO ACTION')]
    private Day $day;

    /**
     * Initializes class instance.
     */
    public function __construct(Profile $host, Day $day)
    {
        $this->host = $host;
        $this->day = $day;
    }

    public function setId(string $id): GuestInvitation
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

    public function setHost(Profile $host): self
    {
        $this->host = $host;

        return $this;
    }

    public function getHost(): Profile
    {
        return $this->host;
    }

    /**
     * Get meal day.
     */
    public function getDay(): Day
    {
        return $this->day;
    }

    #[ORM\PrePersist]
    public function beforeCreate(): void
    {
        $this->id = md5($this->host->getUsername() . $this->day->getId());
        $this->createdOn = new DateTime();
    }
}
