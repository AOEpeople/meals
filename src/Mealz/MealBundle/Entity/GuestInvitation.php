<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Mealz\UserBundle\Entity\Profile;

/**
 * Guest invitation entity.
 *
 * @author Chetan Thapliyal <chetan.thapliyal@aoe.com>
 * @ORM\Table(name="guest_invitation")
 * @ORM\Entity(repositoryClass="Mealz\MealBundle\Entity\GuestInvitationRepository")
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
	 * @var \DateTime
	 * @ORM\Column(name="created_on", type="datetime")
	 */
	private $createdOn;

	/**
	 * @var Profile
	 * @ORM\ManyToOne(targetEntity="Mealz\UserBundle\Entity\Profile")
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
	 * Get id
	 *
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}

	/**
	 * Set createdOn
	 *
	 * @param \DateTime $createdOn
	 *
	 * @return GuestInvitation
	 */
	public function setCreatedOn(\DateTime $createdOn)
	{
		$this->createdOn = $createdOn;
		return $this;
	}

	/**
	 * Get createdOn
	 *
	 * @return \DateTime
	 */
	public function getCreatedOn()
	{
		return $this->createdOn;
	}

	/**
	 * Set host
	 *
	 * @param  Profile $host
	 *
	 * @return GuestInvitation
	 */
	public function setHost(Profile $host)
	{
		$this->host = $host;
		return $this;
	}

	/**
	 * Get host
	 *
	 * @return Profile
	 */
	public function getHost()
	{
		return $this->host;
	}

	/**
	 * Set meal day
	 *
	 * @param Day $day
	 *
	 * @return GuestInvitation
	 */
	public function setDay(Day $day)
	{
		$this->day = $day;
		return $this;
	}

	/**
	 * Get meal day
	 *
	 * @return Day
	 */
	public function getDay()
	{
		return $this->day;
	}
}

