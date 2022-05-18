<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service\Logger;

use Psr\Log\LoggerInterface;
use Throwable;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 */
class MealsLogger implements MealsLoggerInterface
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function emergency($message, array $context = []): void
    {
        $this->logger->emergency($message, $context);
    }

    public function alert($message, array $context = []): void
    {
        $this->logger->alert($message, $context);
    }

    public function critical($message, array $context = []): void
    {
        $this->logger->critical($message, $context);
    }

    public function error($message, array $context = []): void
    {
        $this->logger->error($message, $context);
    }

    public function warning($message, array $context = []): void
    {
        $this->logger->warning($message, $context);
    }

    public function notice($message, array $context = []): void
    {
        $this->logger->notice($message, $context);
    }

    public function info($message, array $context = []): void
    {
        $this->logger->info($message, $context);
    }

    public function debug($message, array $context = []): void
    {
        $this->logger->debug($message, $context);
    }

    public function log($level, $message, array $context = []): void
    {
        $this->logger->log($level, $message, $context);
    }

    public function logException(Throwable $exc, string $message = '', array $context = []): void
    {
        $msg = '' === $message ? 'exception occurred' : $message;
        $excChain = [];

        for ($i = 0; $exc; ++$i) {
            $excLog = ['exception' => get_class($exc)];

            if (0 < $exc->getCode()) {
                $excLog['code'] = $exc->getCode();
            }

            $excLog['message'] = $exc->getMessage();
            $excLog['file'] = $exc->getFile() . ':' . $exc->getLine();

            $prev = $exc->getPrevious();
            if (null === $prev) {
                $excLog['stacktrace'] = $exc->getTraceAsString();
            }

            $exc = $prev;
            $excChain['caused by [#' . $i . ']'] = $excLog;
        }

        $this->logger->error($msg, array_merge($excChain, $context));
    }
}
