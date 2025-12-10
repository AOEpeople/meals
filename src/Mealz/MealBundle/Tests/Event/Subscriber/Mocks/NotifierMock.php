<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Event\Subscriber\Mocks;

use App\Mealz\MealBundle\Service\Notification\MessageInterface;
use App\Mealz\MealBundle\Service\Notification\NotifierInterface;
use Override;

final class NotifierMock implements NotifierInterface
{
    public MessageInterface $inputMessage;
    public bool $outputIsNotified;

    #[Override]
    public function send(MessageInterface $message): bool
    {
        $this->inputMessage = $message;

        return $this->outputIsNotified;
    }
}
