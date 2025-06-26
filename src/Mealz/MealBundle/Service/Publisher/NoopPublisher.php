<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Publisher;

use Override;

final class NoopPublisher implements PublisherInterface
{
    #[Override]
    public function publish(string $topic, array $data, string $type): bool
    {
        return true;
    }
}
