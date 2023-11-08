<?php

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Category;
use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\EventParticipation;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Central service to link to actions.
 *
 * This is done for portability. Should we decide to switch the urls to use the title instead of the
 * id, then we just have to change this here and not in all templates. Also this is easier readable in templates.
 */
class Link
{
    protected RouterInterface $router;

    public function __construct(RouterInterface $router)
    {
        $this->router = $router;
    }

    /**
     * @param $object
     * @param null $action
     * @param int  $referenceType
     *
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
        }

        throw new \InvalidArgumentException(sprintf('linking a %s object is not configured.', get_class($object)));
    }

    public function linkMeal(
        Meal $meal,
        string $action = null,
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        $dish = $meal->getDish();
        if (!($dish instanceof Dish)) {
            return '';
        }

        $action = $action ?: 'show';

        if ('show' === $action || 'join' === $action || 'join_someone' === $action || 'accept_offer' === $action) {
            return $this->router->generate('MealzMealBundle_Meal_' . $action, [
                'date' => $meal->getDateTime()->format('Y-m-d'),
                'dish' => $meal->getDish()->getSlug(),
            ], $referenceType);
        }

        if ('newParticipant' === $action) {
            return $this->router->generate('MealzMealBundle_Meal_Participant_new', [
                'date' => $meal->getDateTime()->format('Y-m-d'),
                'dish' => $meal->getDish()->getSlug(),
            ], $referenceType);
        }

        if ('edit' === $action || 'delete' === $action) {
            // admin actions
            return $this->router->generate('MealzMealBundle_Meal_' . $action, ['meal' => $meal->getId()], $referenceType);
        }

        throw new \InvalidArgumentException(sprintf('linking to "%s" action on a %s object is not configured.', $action, get_class($meal)));
    }

    public function linkParticipant(
        Participant $participant,
        string $action = null,
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        $action = $action ?? 'edit';

        if ('edit' === $action || 'delete' === $action || 'confirm' === $action || 'swap' === $action || 'unswap' === $action) {
            return $this->router->generate('MealzMealBundle_Meal_Participant_' . $action, ['participant' => $participant->getId()], $referenceType);
        }

        throw new \InvalidArgumentException(sprintf('linking to "%s" action on a %s object is not configured.', $action, get_class($participant)));
    }

    public function linkEventParticipant(
        Participant $participant,
        string $action = null,
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        $action = $action ?? 'edit';

        if ('edit' === $action || 'delete' === $action || 'confirm' === $action || 'swap' === $action || 'unswap' === $action) {
            return $this->router->generate('MealzMealBundle_Event_Participant_' . $action, ['participant' => $participant->getId()], $referenceType);
        }

        throw new \InvalidArgumentException(sprintf('linking to "%s" action on a %s object is not configured.', $action, get_class($participant)));
    }

    public function linkDish(
        Dish $dish,
        string $action = null,
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        $action = $action ?? 'edit';

        if ('delete' === $action) {
            // admin actions
            return $this->router->generate('MealzMealBundle_Dish_' . $action, ['slug' => $dish->getSlug()], $referenceType);
        }

        if ('edit' === $action) {
            return $this->router->generate('MealzMealBundle_Dish_Form_preFilled', ['slug' => $dish->getSlug()], $referenceType);
        }

        throw new \InvalidArgumentException(sprintf('linking to "%s" action on a %s object is not configured.', $action, get_class($dish)));
    }

    public function linkCategory(
        Category $category,
        $action = null,
        $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ) {
        $parameters = ['slug' => $category->getSlug()];
        if ('edit' === $action) {
            return $this->router->generate(
                'MealzMealBundle_Category_Form_preFilled',
                $parameters,
                $referenceType
            );
        }

        if (null !== $action) {
            return $this->router->generate(
                'MealzMealBundle_Category_' . $action,
                $parameters,
                $referenceType
            );
        }

        throw new \InvalidArgumentException(sprintf('linking to "%s" action on a %s object is not configured.', $action, get_class($category)));
    }

    public function linkEvent(
        EventParticipation $eventParticipation,
        Day $day,
        string $action = null,
        int $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH
    ): string {
        $action = $action ?: 'show';

        if ('show' === $action || 'join' === $action || 'join_someone' === $action) {
            return $this->router->generate('MealzMealBundle_Event_' . $action, [
                'date' => $day->getDateTime()->format('Y-m-d'),
                'event' => $eventParticipation->getEvent()->getSlug(),
            ], $referenceType);
        }

        if ('newParticipant' === $action) {
            return $this->router->generate('MealzMealBundle_Event_Participant_new', [
                'date' => $day->getDateTime()->format('Y-m-d'),
                'event' => $eventParticipation->getEvent()->getSlug(),
            ], $referenceType);
        }

        throw new \InvalidArgumentException(sprintf('linking to "%s" action on a %s object is not configured.', $action, get_class($eventParticipation)));
    }
}
