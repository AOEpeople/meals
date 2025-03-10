<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\EventSubscriber;

use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

final class ExceptionSubscriber implements EventSubscriberInterface
{
    #[Override]
    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::EXCEPTION => ['handleException', 5],
        ];
    }

    public function handleException(ExceptionEvent $event): void
    {
        if (!$event->getRequest()->isXmlHttpRequest()) {
            return;
        }

        $exception = $event->getThrowable();
        if ($exception instanceof AuthenticationException || $exception instanceof AccessDeniedException) {
            $event->setResponse(new JsonResponse(null, \Symfony\Component\HttpFoundation\Response::HTTP_UNAUTHORIZED));
        }
    }
}
