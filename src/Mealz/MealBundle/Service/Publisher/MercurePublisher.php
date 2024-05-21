<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Publisher;

use App\Mealz\MealBundle\Service\Logger\MealsLoggerInterface;
use Exception;
use JsonException;
use Symfony\Component\Mercure\HubInterface;
use Symfony\Component\Mercure\Update;

class MercurePublisher implements PublisherInterface
{
    private HubInterface $hub;
    private MealsLoggerInterface $logger;

    public function __construct(HubInterface $hub, MealsLoggerInterface $logger)
    {
        $this->hub = $hub;
        $this->logger = $logger;
    }

    public function publish(string $topic, array $data, string $type): bool
    {
        $result = '';

        try {
            $payload = json_encode($data, JSON_THROW_ON_ERROR);
            $update = new Update($topic, $payload, true, null, $type, null);
            $result = $this->hub->publish($update);
        } catch (JsonException $jex) {
            $this->logger->logException($jex, 'json encode error', [
                'topic' => $topic,
                'type' => $type,
            ]);
        } catch (Exception $oex) {
            $this->logger->logException($oex, 'message publish error', [
                'topic' => $topic,
                'type' => $type,
            ]);
        }

        return '' !== $result;
    }
}
