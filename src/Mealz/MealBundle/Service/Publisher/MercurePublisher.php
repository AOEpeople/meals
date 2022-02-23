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
     *  publish data to a topic
     * @return bool                         //  on success returns true
     * @throws JsonException
     */
    public function publish(string $topic, array $data) : bool
    {
        $payload = json_encode($data, JSON_THROW_ON_ERROR);
        $update = new Update($topic, $payload, false, null, null, null);

        return ($this->hub->publish($update) !== '');
    }
}