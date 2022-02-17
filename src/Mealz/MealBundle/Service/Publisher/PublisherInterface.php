<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Publisher;

interface PublisherInterface
{
    /**
     *  publish a payload to an array of topics
     *
     *  @param  string|string[]  $topics     //  array of strings or string to publish to
     *  @param  string           $payload    //  Json encoded Payload of the message
     *  @return bool                         //  on success returns true
     */
    public function publish($topics, string $payload): bool;
}