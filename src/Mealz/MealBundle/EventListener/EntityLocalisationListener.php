<?php


namespace Mealz\MealBundle\EventListener;

use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Mealz\MealBundle\Entity\Category;
use Mealz\MealBundle\Entity\Dish;
use Mealz\MealBundle\Entity\DishVariation;
use Mealz\MealBundle\Service\HttpHeaderUtility;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class EntityLocalisationListener extends LocalisationListener
{

    /**
     * @var RequestStack
     */
    protected $requestStack;

    public function __construct(HttpHeaderUtility $httpHeaderUtility, RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        parent::__construct($httpHeaderUtility);
    }

    /**
     * set localisation for dish and category objects
     *
     * @param LifecycleEventArgs $args
     */
    public function postLoad(LifecycleEventArgs $args)
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
