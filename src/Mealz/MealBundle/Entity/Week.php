<?php

namespace App\Mealz\MealBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="week")
 */
class Week extends AbstractMessage
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @var int
     */
    private $id;

    /**
     * @ORM\Column(type="smallint", nullable=FALSE)
     *
     * @var int
     */
    private $year;

    /**
     * @ORM\Column(type="smallint", nullable=FALSE)
     *
     * @var int
     */
    private $calendarWeek;

    /**
     * @ORM\OneToMany(targetEntity="Day", mappedBy="week", cascade={"all"})
     * @ORM\OrderBy({"dateTime" = "ASC"})
     *
     * @var ArrayCollection
     */
    private $days;

    public function __construct()
    {
        $this->days = new ArrayCollection();
    }

    /**
     * @return int
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
    public function setDays($days): void
    {
        $this->days = $days;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @param int $year
     */
    public function setYear($year): void
    {
        $this->year = $year;
    }

    /**
     * @return int
     */
    public function getCalendarWeek()
    {
        return $this->calendarWeek;
    }

    /**
     * @param int $calendarWeek
     */
    public function setCalendarWeek($calendarWeek): void
    {
        $this->calendarWeek = $calendarWeek;
    }

    public function getStartTime(): DateTime
    {
        $datetime = $this->getWeekDateTime();
        $datetime->setTime(0, 0);

        return $datetime;
    }

    public function getEndTime(): DateTime
    {
        $endTime = $this->getWeekDateTime();
        $endTime->modify('+4 days 23:59:59');

        return $endTime;
    }

    private function getWeekDateTime(): DateTime
    {
        $dateTime = new DateTime();
        $dateTime->setISODate($this->getYear(), $this->getCalendarWeek());

        return $dateTime;
    }
}
