<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Service\Doorman;
use App\Mealz\MealBundle\Service\Logger\MealsLoggerInterface;
use App\Mealz\UserBundle\Entity\Profile;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;
use Throwable;

abstract class BaseController extends AbstractController
{
    private \App\Mealz\MealBundle\Service\Doorman $doorman;
    private TranslatorInterface $dataCollectorTranslator;
    private LoggerInterface $logger;
    public function __construct(Doorman $doorman, TranslatorInterface $dataCollectorTranslator, LoggerInterface $logger)
    {
        $this->doorman = $doorman;
        $this->dataCollectorTranslator = $dataCollectorTranslator;
        $this->logger = $logger;
    }
    public static function getSubscribedServices(): array
    {
        $services = parent::getSubscribedServices();
        $services['logger'] = '?' . MealsLoggerInterface::class;
        $services['mealz_meal.doorman'] = '?' . Doorman::class;
        $services['monolog.logger.balance'] = '?' . LoggerInterface::class;
        $services['translator'] = '?' . TranslatorInterface::class;

        return $services;
    }

    /**
     * @return Doorman
     */
    protected function getDoorman()
    {
        return $this->doorman;
    }

    protected function getProfile(): ?Profile
    {
        return $this->getUser() ? $this->getUser()->getProfile() : null;
    }

    /**
     * @param mixed  $message
     * @param string $severity "danger", "warning", "info", "success"
     */
    protected function addFlashMessage($message, string $severity): void
    {
        $this->get('session')->getFlashBag()->add($severity, $message);
    }

    protected function ajaxSessionExpiredRedirect(): JsonResponse
    {
        $message = $this->dataCollectorTranslator->trans('session.expired', [], 'messages');
        $this->addFlashMessage($message, 'info');
        $response = [
            'redirect' => $this->generateUrl('MealzUserBundle_login'),
        ];

        return new JsonResponse($response);
    }

    /**
     * @param Exception $exc     Exception to log
     * @param string    $message Additional message
     * @param array     $context Name-value data describing the execution state when exception occurred
     */
    protected function logException(Throwable $exc, string $message = '', array $context = []): void
    {
        $this->logger->logException($exc, $message, $context);
    }
}
