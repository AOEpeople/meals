<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Publisher;

class NoopPublisher implements PublisherInterface
{
    public function publish(string $topic, array $data, string $type): bool
    {
        return true;
    }
}
