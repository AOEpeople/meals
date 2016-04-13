<?php
/**
 * Created by PhpStorm.
 * User: jonathan.klauck
 * Date: 13.04.2016
 * Time: 13:29
 */

namespace Mealz\MealBundle\Entity;

class Week
{
    /*
     * @var \DateTime $starTime
     */
    private $startTime;

    /*
     * @var \DateTime $endTime
     */
    private $endTime;

    /*
     * @var int $mealsCount
     */
    private $mealsCount;

    /*
     * @var array $days
     */
    private $days;

    /**
     * @return \DateTime
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * @param \DateTime $startTime
     */
    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    /**
     * @return \DateTime
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * @param \DateTime $endTime
     */
    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }

    /**
     * @return int
     */
    public function getMealsCount()
    {
        return $this->mealsCount;
    }

    /**
     * @param int $mealsCount
     */
    public function setMealsCount($mealsCount)
    {
        $this->mealsCount = $mealsCount;
    }

    /**
     * @return array
     */
    public function getDays()
    {
        return $this->days;
    }

    /**
     * @param array $days
     */
    public function setDays($days)
    {
        $this->days = $days;
    }
}