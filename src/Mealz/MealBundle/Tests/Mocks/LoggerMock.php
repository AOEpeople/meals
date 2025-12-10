<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Mocks;

use Psr\Log\LoggerInterface;
use Stringable;

final class LoggerMock implements LoggerInterface
{
    public array $logs = [];

    public function emergency(Stringable|string $message, array $context = []): void
    {
        $this->logs['emergence'][] = [
            'message' => $message,
            'context' => $context,
        ];
    }

    public function alert(Stringable|string $message, array $context = []): void
    {
        $this->logs['alert'][] = [
            'message' => $message,
            'context' => $context,
        ];
    }

    public function critical(Stringable|string $message, array $context = []): void
    {
        $this->logs['critical'][] = [
            'message' => $message,
            'context' => $context,
        ];
    }

    public function error(Stringable|string $message, array $context = []): void
    {
        $this->logs['error'][] = [
            'message' => $message,
            'context' => $context,
        ];
    }

    public function warning(Stringable|string $message, array $context = []): void
    {
        $this->logs['warning'][] = [
            'message' => $message,
            'context' => $context,
        ];
    }

    public function notice(Stringable|string $message, array $context = []): void
    {
        $this->logs['notice'][] = [
            'message' => $message,
            'context' => $context,
        ];
    }

    public function info(Stringable|string $message, array $context = []): void
    {
        $this->logs['info'][] = [
            'message' => $message,
            'context' => $context,
        ];
    }

    public function debug(Stringable|string $message, array $context = []): void
    {
        $this->logs['debug'][] = [
            'message' => $message,
            'context' => $context,
        ];
    }

    public function log($level, Stringable|string $message, array $context = []): void
    {
        $this->logs[$level][] = [
            'message' => $message,
            'context' => $context,
        ];
    }
}
