<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\DataFixtures\ORM;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Week;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;

class LoadDays extends Fixture implements OrderedFixtureInterface
{
    /**
     * Constant to declare load order of fixture.
     */
    private const ORDER_NUMBER = 3;

    protected ObjectManager $objectManager;

    /**
     * @var Week[]
     */
    protected array $weeks = [];

    protected int $counter = 0;

    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $this->objectManager = $manager;
        $this->loadWeeks();

        foreach ($this->weeks as $week) {
            $startTime = clone $week->getStartTime();
            $startTime->setTime(12, 0);

            for ($i = 0; $i < 5; ++$i) {
                $dateTime = (clone $startTime)->modify('+' . $i . ' days');
                $lockDateTime = (clone $dateTime)->modify('-1 day 16:00');

                $this->addDay($week, $dateTime, $lockDateTime);
            }
        }

        $this->objectManager->flush();
    }

    private function addDay(Week $week, DateTime $dateTime, DateTime $lockDateTime): void
    {
        $day = new Day();
        $day->setWeek($week);
        $day->setDateTime($dateTime);
        $day->setLockParticipationDateTime($lockDateTime);

        $this->objectManager->persist($day);
        $this->addReference('day-' . $this->counter++, $day);
    }

    public function getOrder(): int
    {
        // load as third
        return self::ORDER_NUMBER;
    }

    protected function loadWeeks(): void
    {
        foreach (array_keys($this->referenceRepository->getReferencesByClass()[Week::class]) as $key) {
            // we can't just use $reference here, because
            // getReference() does some doctrine magic that getReferences() does not
            $this->weeks[] = $this->getReference($key, Week::class);
        }
    }
}
