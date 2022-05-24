<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Notification;

interface MessageInterface
{
    public function getContent(): string;
}
