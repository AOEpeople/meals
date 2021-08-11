<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\DataFixtures\ORM;

use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Week;
use Exception;

/**
 * Fixtures Load the Days
 */
class LoadDays extends Fixture implements OrderedFixtureInterface
{
    /**
     * Constant to declare load order of fixture
     */
    private const ORDER_NUMBER = 3;

    protected ObjectManager $objectManager;

    /**
     * @var Week[]
     */
    protected array $weeks = [];

    protected int $counter = 0;

    /**
     * @inheritDoc
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $this->objectManager = $manager;
        $this->loadWeeks();

        foreach ($this->weeks as $week) {
            $startTime = $week->getStartTime();

            $day = new Day();
            $day->setWeek($week);
            $day->setDateTime($startTime);
            $lockDateTime = new DateTime($startTime->format('Y-m-d H:i:s') . ' -1 day');
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
                $lockDateTime = new DateTime($day->getDateTime()->format('Y-m-d H:i:s') . ' -1 day');
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
     */
    public function getOrder(): int
    {
        // load as third
        return self::ORDER_NUMBER;
    }

    /**
     * Load the Weeks
     */
    protected function loadWeeks(): void
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
