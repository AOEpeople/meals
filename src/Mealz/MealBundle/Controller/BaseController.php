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
    public static function getSubscribedServices(): array
    {
        $services = parent::getSubscribedServices();
        $services['monolog.logger.balance'] = '?' . LoggerInterface::class;
        $services['translator'] = '?' . TranslatorInterface::class;

        return $services;
    }

    protected function getProfile(): ?Profile
    {
        return $this->getUser() ? $this->getUser()->getProfile() : null;
    }

    /**
     * @return array<string, string>
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
