<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\DataFixtures\ORM;

use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Event\WeekUpdateEvent;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use Override;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

final class LoadCombinations extends Fixture implements OrderedFixtureInterface
{
    /**
     * Constant to declare load order of fixture.
     */
    private const ORDER_NUMBER = 8;

    protected ObjectManager $objectManager;

    /**
     * @var Week[]
     */
    protected array $weeks = [];

    private EventDispatcherInterface $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * @throws Exception
     */
    #[Override]
    public function load(ObjectManager $manager): void
    {
        $this->objectManager = $manager;
        $this->loadWeeks();
        $this->loadCombination();

        $this->objectManager->flush();
    }

    #[Override]
    public function getOrder(): int
    {
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

    private function loadCombination(): void
    {
        foreach ($this->weeks as $week) {
            $this->eventDispatcher->dispatch(new WeekUpdateEvent($week));
        }
    }
}
