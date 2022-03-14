<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Publisher;

use GuzzleHttp\Exception\BadResponseException;
use JsonException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercurePublisher implements PublisherInterface
{
    private HubInterface $hub;
    private LoggerInterface $logger;

    public function __construct(HubInterface $hub, LoggerInterface $logger)
    {
        $this->hub = $hub;
        $this->logger = $logger;
    }

    /**
     * publish data to a topic.
     *
     * @throws JsonException
     */
    public function publish(string $topic, array $data): bool
    {
        try {
            $payload = json_encode($data, JSON_THROW_ON_ERROR);
            $update = new Update($topic, $payload, false, null, null, null);

            return '' !== $this->hub->publish($update);
        } catch (BadResponseException $e) {
            $this->logger->error('publish error', ['error' => $e->getMessage()]);
        }

        return false;
    }
}
