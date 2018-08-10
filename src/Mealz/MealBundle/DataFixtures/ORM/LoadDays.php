<?php

namespace Mealz\MealBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mealz\MealBundle\Entity\Day;
use Mealz\MealBundle\Entity\Dish;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\Week;

/**
 * Fixtures Load the Days
 * Class LoadDays
 * @package Mealz\MealBundle\DataFixtures\ORM
 */
class LoadDays extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Constant to declare load order of fixture
     */
    const ORDER_NUMBER = 3;

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var Week[]
     */
    protected $weeks = array();

    protected $counter = 0;

    /**
     * load the Object
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->objectManager = $manager;
        $this->loadWeeks();

        foreach ($this->weeks as $week) {
            $startTime = $week->getStartTime();

            $day = new Day();
            $day->setWeek($week);
            $day->setDateTime($startTime);
            $lockDateTime = new \DateTime($startTime->format('Y-m-d H:i:s') . ' -1 day');
            $lockDateTime->modify('16:00');
            $day->setLockParticipationDateTime($lockDateTime);
            $this->objectManager->persist($day);
            $this->addReference('day-' . $this->counter++, $day);

            for ($i = 1; $i < 5; $i++) {
                $day = new Day();
                $day->setWeek($week);
                $time = clone($startTime);
                $time->modify('+' . $i . ' days');
                $day->setDateTime($time);
                $lockDateTime = new \DateTime($day->getDateTime()->format('Y-m-d H:i:s') . ' -1 day');
                $lockDateTime->modify('16:00');
                $day->setLockParticipationDateTime($lockDateTime);
                $this->objectManager->persist($day);
                $this->addReference('day-' . $this->counter++, $day);
            }
        }

        $this->objectManager->flush();
    }


    /**
     * get the Order of Fixtures Loading
     * @return mixed
     */
    public function getOrder()
    {
        /**
         * load as third
         */
        return self::ORDER_NUMBER;
    }

    /**
     * Load the Weeks
     */
    protected function loadWeeks()
    {
        foreach ($this->referenceRepository->getReferences() as $referenceName => $reference) {
            if ($reference instanceof Week) {
                // we can't just use $reference here, because
                // getReference() does some doctrine magic that getReferences() does not
                $this->weeks[] = $this->getReference($referenceName);
            }
        }
    }


}