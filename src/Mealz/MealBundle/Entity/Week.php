<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Week
 *
 * @ORM\Table(name="week")
 * @ORM\Entity(repositoryClass="WeekRepository")
 */
class Week extends AbstractMessage
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @var integer $id
     */
    private $id;

    /**
     * @ORM\Column(type="smallint", nullable=FALSE)
     * @var integer $year
     */
    private $year;

    /**
     * @ORM\Column(type="smallint", nullable=FALSE)
     * @var integer $calendarWeek
     */
    private $calendarWeek;

    /**
     * @ORM\OneToMany(targetEntity="Day", mappedBy="week")
     * @var ArrayCollection $days
     */
    private $days;

    public function __construct()
    {
        $this->days = new ArrayCollection();
    }

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return ArrayCollection
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * @param ArrayCollection $days
     */
    public function setDays($days)
    {
        $this->days = $days;
    }

    /**
     * @return integer
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param integer $year
     */
    public function setYear($year)
    {
        $this->year = $year;
    }

    /**
     * @return integer
     */
    public function getCalendarWeek()
    {
        return $this->calendarWeek;
    }

    /**
     * @param integer $calendarWeek
     */
    public function setCalendarWeek($calendarWeek)
    {
        $this->calendarWeek = $calendarWeek;
    }

    public function getStartTime()
    {
        return $this->getWeekDateTime();
    }

    public function getEndTime()
    {
        $endTime = $this->getWeekDateTime();
        $endTime->modify('+4 days');
        return $endTime;
    }

    private function getWeekDateTime()
    {
        $dateTime = new \DateTime();
        $dateTime->setISODate($this->getYear(), $this->getCalendarWeek());
        return $dateTime;
    }
}