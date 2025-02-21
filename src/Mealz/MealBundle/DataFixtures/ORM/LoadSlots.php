<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\DataFixtures\ORM;

use App\Mealz\MealBundle\Entity\Slot;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Override;

final class LoadSlots extends Fixture implements OrderedFixtureInterface
{
    /**
     * Constant to declare load order of fixture.
     */
    private const ORDER_NUMBER = 4;

    #[Override]
    public function load(ObjectManager $manager): void
    {
        $slotItems = [
            [
                'title' => 'Active w/o limit',
                'limit' => 0,
                'deleted' => false,
                'disabled' => false,
                'slug' => 'active-wo-limit',
            ],
            [
                'title' => 'Active w/ limit',
                'limit' => 10,
                'deleted' => false,
                'disabled' => false,
                'slug' => 'active-w-limit',
            ],
            [
                'title' => 'Inactive',
                'limit' => 0,
                'deleted' => false,
                'disabled' => true,
                'slug' => 'inactive',
            ],
            [
                'title' => 'Deleted',
                'limit' => 0,
                'deleted' => true,
                'disabled' => false,
                'slug' => 'deleted',
            ],
        ];

        foreach ($slotItems as $key => $item) {
            $slot = new Slot();
            $slot->setTitle($item['title']);
            $slot->setLimit($item['limit']);
            $slot->setDeleted($item['deleted']);
            $slot->setDisabled($item['disabled']);
            $slot->setSlug($item['slug']);

            $manager->persist($slot);
            $this->addReference('slot-' . ++$key, $slot);
        }

        $manager->flush();
    }

    #[Override]
    public function getOrder(): int
    {
        return self::ORDER_NUMBER;
    }
}
