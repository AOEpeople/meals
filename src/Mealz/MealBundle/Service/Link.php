<?php

namespace Mealz\MealBundle\Service;

use Mealz\MealBundle\Entity\Category;
use Mealz\MealBundle\Entity\Dish;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\Participant;
use Symfony\Bundle\FrameworkBundle\Routing\Router;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * Central service to link to actions
 *
 * This is done for portability. Should we decide to switch the urls to use the title instead of the
 * id, then we just have to change this here and not in all templates. Also this is easier readable in templates.
 */
class Link
{

    /**
     * @var Router
     */
    protected $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * @param $object
     * @param null $action
     * @param int $referenceType
     * @return string
     */
    public function link($object, $action = null, $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        if ($object instanceof Meal) {
            return $this->linkMeal($object, $action, $referenceType);
        } elseif ($object instanceof Participant) {
            return $this->linkParticipant($object, $action, $referenceType);
        } elseif ($object instanceof Dish) {
            return $this->linkDish($object, $action, $referenceType);
        } elseif ($object instanceof Category) {
            return $this->linkCategory($object, $action, $referenceType);
        } else {
            throw new \InvalidArgumentException(sprintf(
                'linking a %s object is not configured.',
                get_class($object)
            ));
        }
    }

    /**
     * @param Meal $meal
     * @param null $action
     * @param int $referenceType
     * @return string
     */
    public function linkMeal(Meal $meal, $action = null, $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $action = $action ?: 'show';
        if (is_null($meal->getDish()) === true || $meal->getDish() instanceof Dish === false) {
            return;
        }

        if ($action === 'show' || $action === 'join' || $action === 'join_someone' || $action === 'accept_offer') {
            return $this->router->generate('MealzMealBundle_Meal_' . $action, array(
                'date' => $meal->getDateTime()->format('Y-m-d'),
                'dish' => $meal->getDish()->getSlug(),
            ), $referenceType);
        } elseif ($action === 'newParticipant') {
            return $this->router->generate('MealzMealBundle_Participant_new', array(
                'date' => $meal->getDateTime()->format('Y-m-d'),
                'dish' => $meal->getDish()->getSlug(),
            ), $referenceType);
        } elseif ($action === 'edit' || $action === 'delete') {
            // admin actions
            return $this->router->generate('MealzMealBundle_Meal_' . $action, array('meal' => $meal->getId()), $referenceType);
        }

        throw new \InvalidArgumentException(sprintf(
            'linking to "%s" action on a %s object is not configured.',
            $action,
            get_class($meal)
        ));
    }

    /**
     * @param Participant $participant
     * @param null $action
     * @param int $referenceType
     * @return string
     */
    public function linkParticipant(Participant $participant, $action = null, $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $action = $action ?: 'edit';
        if ($action === 'edit' || $action === 'delete' || $action === 'confirm' || $action === 'swap' || $action === 'unswap') {
            return $this->router->generate('MealzMealBundle_Participant_' . $action, array('participant' => $participant->getId()), $referenceType);
        } else {
            throw new \InvalidArgumentException(sprintf(
                'linking to "%s" action on a %s object is not configured.',
                $action,
                get_class($participant)
            ));
        }
    }

    /**
     * @param Dish $dish
     * @param null $action
     * @param int $referenceType
     * @return string
     */
    public function linkDish(Dish $dish, $action = null, $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        $action = $action ?: 'edit';
        if ($action === 'delete') {
            // admin actions
            return $this->router->generate('MealzMealBundle_Dish_' . $action, array('slug' => $dish->getSlug()), $referenceType);
        } elseif ($action === 'edit') {
            return $this->router->generate('MealzMealBundle_Dish_Form_preFilled', array('slug' => $dish->getSlug()), $referenceType);
        } else {
            throw new \InvalidArgumentException(sprintf(
                'linking to "%s" action on a %s object is not configured.',
                $action,
                get_class($dish)
            ));
        }
    }

    public function linkCategory(
        Category $category,
        $action = null,
        $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ) {
        $parameters = array('slug' => $category->getSlug());
        if ($action === 'edit') {
            return $this->router->generate(
                'MealzMealBundle_Category_Form_preFilled',
                $parameters,
                $referenceType
            );
        } elseif (null !== $action) {
            return $this->router->generate(
                'MealzMealBundle_Category_' . $action,
                $parameters,
                $referenceType
            );
        } else {
            throw new \InvalidArgumentException(sprintf(
                'linking to "%s" action on a %s object is not configured.',
                $action,
                get_class($category)
            ));
        }
    }
}
