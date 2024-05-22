<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\UserBundle\Entity\Profile;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

abstract class BaseController extends AbstractController
{
    /**
     * @return (\Symfony\Contracts\Service\Attribute\SubscribedService|string)[]
     *
     * @psalm-return array<\Symfony\Contracts\Service\Attribute\SubscribedService|string>
     */
    public static function getSubscribedServices(): array
    {
        $services = parent::getSubscribedServices();
        $services['monolog.logger.balance'] = '?' . LoggerInterface::class;
        $services['translator'] = '?' . TranslatorInterface::class;

        return $services;
    }

    protected function getProfile(): ?Profile
    {
        $user = $this->getUser();
        if ($user instanceof Profile) {
            return $user->getProfile();
        }

        return null;
    }

    /**
     * @return array<string, array{code?: int, exception: string, file: string, message: string, stacktrace?: string}>
     */
    protected function getTrace(Throwable $exc): array
    {
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

        return $excChain;
    }
}
