<?php


namespace Mealz\MealBundle\EventListener;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Mealz\MealBundle\Entity\Category;
use Mealz\MealBundle\Entity\Dish;
use Mealz\MealBundle\Service\HttpHeaderUtility;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class LocalisationListener {

	/**
	 * @var RequestStack
	 */
	protected $requestStack;

	/**
	 * @var HttpHeaderUtility
	 */
	protected $httpHeaderUtility;

	public function __construct(RequestStack $requestStack, HttpHeaderUtility $httpHeaderUtility) {
		$this->requestStack = $requestStack;
		$this->httpHeaderUtility = $httpHeaderUtility;
	}

	/**
	 * set localisation for dish objects
	 *
	 * @param LifecycleEventArgs $args
	 */
	public function postLoad(LifecycleEventArgs $args) {
		$entity = $args->getEntity();

		if($entity instanceof Dish || $entity instanceof Category) {
			$currentLocale = 'en';
			if($this->requestStack->getCurrentRequest()) {
				$locale = substr($this->requestStack->getCurrentRequest()->getLocale(), 0, 2);
				if($locale === 'de') {
					$currentLocale = 'de';
				}
			}
			$entity->setCurrentLocale($currentLocale);
		}
	}

	public function onKernelRequest(GetResponseEvent $getResponseEvent) {
		$request = $getResponseEvent->getRequest();
		if($request->headers->has('Accept-Language')) {
			$locale = $this->httpHeaderUtility->getLocaleFromAcceptLanguageHeader($request->headers->get('Accept-Language'));
			$request->setLocale('de');
		}
	}

	public function onKernelResponse(FilterResponseEvent $filterResponseEvent) {
		$response = $filterResponseEvent->getResponse();
		$response->headers->add('Vary: Accept-Language');
	}

}