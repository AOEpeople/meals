<?php


namespace Mealz\MealBundle\EventListener;

use Mealz\MealBundle\Service\HttpHeaderUtility;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class LocalisationListener {

	/**
	 * @var HttpHeaderUtility
	 */
	protected $httpHeaderUtility;

	/**
	 * @var string
	 */
	protected $locale = 'en';

	public function __construct(HttpHeaderUtility $httpHeaderUtility)
	{
		$this->httpHeaderUtility = $httpHeaderUtility;
	}

	public function onKernelRequest(GetResponseEvent $getResponseEvent) {
		$request = $getResponseEvent->getRequest();
		$cookies = $request->cookies;
        if($cookies->has('locale')) {
            $this->locale = $cookies->get('locale');
        } elseif ($request->headers->has('Accept-Language')) {
            $headerLang = $request->headers->get('Accept-Language');
            $this->locale = $this->httpHeaderUtility->getLocaleFromAcceptLanguageHeader($headerLang);
        }
        $request->setLocale($this->locale);
	}

	public function onKernelResponse(FilterResponseEvent $filterResponseEvent) {
		$response = $filterResponseEvent->getResponse();
		$response->headers->add('Vary: Accept-Language');
	}

	public function getLocale()
	{
		return $this->locale;
	}
}