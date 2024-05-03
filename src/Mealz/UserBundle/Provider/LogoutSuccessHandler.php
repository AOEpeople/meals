<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\Provider;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Event\LogoutEvent;

/**
 * Class LogoutSuccessHandler.
 */
class LogoutSuccessHandler implements EventSubscriberInterface
{
    private string $logoutUrl;

    public function __construct(string $logoutUrl, string $baseUri)
    {
        $this->logoutUrl = $logoutUrl . '?redirect_uri=' . $baseUri;
    }

    /**
     * Redirects on successful logout.
     */
    public function onLogout(LogoutEvent $event): void
    {
        $request = $event->getRequest();

        if ($request->isXmlHttpRequest()) {
            $response = new JsonResponse([
                'payload' => null,
                'error' => [],
                'redirect' => $this->logoutUrl,
            ]);
            $response->setStatusCode(\Symfony\Component\HttpFoundation\Response::HTTP_FOUND);
            $event->setResponse($response);

            return;
        }

        $response = new RedirectResponse($this->logoutUrl);
        $event->setResponse($response);
    }

    /**
     * @inheritdoc
     * @return string[]
     */
    public static function getSubscribedEvents(): array
    {
        return [
            LogoutEvent::class => 'onLogout',
        ];
    }
}
