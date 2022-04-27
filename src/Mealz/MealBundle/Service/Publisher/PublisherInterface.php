<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Publisher;

interface PublisherInterface
{
    /**
     * Publishes data to a given topic to the configured message server.
     */
    public function publish(string $topic, array $data, string $type): bool;
}
