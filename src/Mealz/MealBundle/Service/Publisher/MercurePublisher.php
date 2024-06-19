<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Publisher;

use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercurePublisher implements PublisherInterface
{
    public function __construct(
        private HubInterface $hub,
        private LoggerInterface $logger
    ) {
    }

    public function publish(string $topic, array $data, string $type): bool
    {
        $result = '';

        try {
            $payload = json_encode($data, JSON_THROW_ON_ERROR);
            $update = new Update($topic, $payload, true, null, $type, null);
            $result = $this->hub->publish($update);
        } catch (Exception $e) {
            $this->logger->error('message publish error', [
                'topic' => $topic,
                'type' => $type,
                'trace' => $e->getTraceAsString(),
            ]);
        }

        return '' !== $result;
    }
}
