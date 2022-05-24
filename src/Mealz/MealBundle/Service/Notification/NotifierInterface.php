<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Notification;

interface NotifierInterface
{
    /**
     * @param MessageInterface $message Alert message
     */
    public function send(MessageInterface $message): bool;
}
