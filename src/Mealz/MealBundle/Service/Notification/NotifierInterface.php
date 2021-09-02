<?php

namespace App\Mealz\MealBundle\Service\Notification;

use Symfony\Contracts\HttpClient\ResponseInterface;

/**
 * NotificationService interface.
 */
interface NotifierInterface
{
    /**
     * @param string $message Alert message
     */
    public function sendAlert(string $message): ?ResponseInterface;
}
