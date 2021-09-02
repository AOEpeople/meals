<?php

namespace App\Mealz\MealBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;
use App\Mealz\MealBundle\Entity\Category;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Service\HttpHeaderUtility;
use Symfony\Component\HttpFoundation\RequestStack;

class EntityLocalisationListener extends LocalisationListener
{
    protected RequestStack $requestStack;

    public function __construct(HttpHeaderUtility $httpHeaderUtility, RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        parent::__construct($httpHeaderUtility);
    }

    /**
     * set localisation for dish and category objects
     */
    public function postLoad(LifecycleEventArgs $args): void
    {
        $entity = $args->getEntity();

        /**
         * @TODO: Refactor to use an interface or abstract class, which all the 3 classes below have in common
         */
        if ($entity instanceof Dish || $entity instanceof Category || $entity instanceof DishVariation) {
            $currentLocale = 'en';
            if ($this->requestStack->getCurrentRequest()) {
                $locale = substr($this->requestStack->getCurrentRequest()->getLocale(), 0, 2);
                if ($locale === 'de') {
                    $currentLocale = 'de';
                }
            }
            $entity->setCurrentLocale($currentLocale);
        }
    }
}
