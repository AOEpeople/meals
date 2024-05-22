<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\EventListener;

use App\Mealz\MealBundle\Service\HttpHeaderUtility;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class LocalisationListener implements EventSubscriberInterface
{
    protected HttpHeaderUtility $httpHeaderUtility;

    protected string $locale = 'en';

    public function __construct(HttpHeaderUtility $httpHeaderUtility)
    {
        $this->httpHeaderUtility = $httpHeaderUtility;
    }

    public function onKernelRequest(RequestEvent $getResponseEvent): void
    {
        $request = $getResponseEvent->getRequest();
        $cookies = $request->cookies;
        if ($cookies->has('locale')) {
            $this->locale = $cookies->get('locale');
        } elseif ($request->headers->has('Accept-Language')) {
            $headerLang = $request->headers->get('Accept-Language');
            $this->locale = $this->httpHeaderUtility->getLocaleFromAcceptLanguageHeader($headerLang);
        }
        $request->setLocale($this->locale);
    }

    public function onKernelResponse(ResponseEvent $filterResponseEvent): void
    {
        $response = $filterResponseEvent->getResponse();
        $response->headers->set('Vary', 'Accept-Language');
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    /**
     * @return (int|string)[][][]
     *
     * @psalm-return array{'kernel.request': array{0: array{0: 'onKernelRequest', 1: 20}}}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            // must be registered before (i.e. with a higher priority than) the default Locale listener
            KernelEvents::REQUEST => [['onKernelRequest', 20]],
        ];
    }
}
