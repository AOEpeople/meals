<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Publisher;

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
     *  publish a payload to an array of topics
     *
     *  @param  string|string[]  $topics     //  array of strings or string to publish to
     *  @param  string           $payload    //  Json encoded Payload of the message
     *  @return bool                         //  on success returns true
     */
    public function publish($topics, string $payload) : bool
    {
        $update = new Update(
            $topics,                    // Array of topics to publish to
            $payload,                   // Payload of the message
            false,                      // If true check for JWT
            null,                       // Transaction ID
            null,                       // Type of message
            null                        // Number of retries
        );
        return ($this->hub->publish($update) != '');
    }
}