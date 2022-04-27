<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Publisher;

use JsonException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercurePublisher implements PublisherInterface
{
    private HubInterface $hub;

    public function __construct(HubInterface $hub)
    {
        $this->hub = $hub;
    }

    /**
     * {@inheritdoc}
     *
     * @throws JsonException
     */
    public function publish(string $topic, array $data, string $type): bool
    {
        $payload = json_encode($data, JSON_THROW_ON_ERROR);
        $update = new Update($topic, $payload, true, null, $type, null);

        return '' !== $this->hub->publish($update);
    }
}
