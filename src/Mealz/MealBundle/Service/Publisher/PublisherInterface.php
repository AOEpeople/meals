<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Publisher;

interface PublisherInterface
{
    /**
     *  publish data to a topic.
     *
     *  @return bool                         //  on success returns true
     */
    public function publish(string $topic, array $data): bool;
}
