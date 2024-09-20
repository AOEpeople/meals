<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\DataFixtures\ORM;

use App\Mealz\MealBundle\Entity\Event;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LoadEvents extends Fixture implements OrderedFixtureInterface
{
    /**
     * Constant to declare load order of fixture.
     */
    private const ORDER_NUMBER = 5;

    public function load(ObjectManager $manager): void
    {
        $eventItems = [
            [
                'title' => 'Afterwork',
                'public' => true,
                'slug' => 'afterwork',
            ],
            [
                'title' => 'Alumni Afterwork',
                'public' => true,
                'slug' => 'alumni-afterwork',
            ],
            [
                'title' => 'Lunch Roulette',
                'public' => true,
                'slug' => 'lunch-roulette',
            ],
        ];

        foreach ($eventItems as $item) {
            $event = new Event();
            $event->setTitle($item['title']);
            $event->setPublic($item['public']);
            $event->setSlug($item['slug']);

            $manager->persist($event);
        }

        $manager->flush();
    }

    public function getOrder(): int
    {
        return self::ORDER_NUMBER;
    }
}
