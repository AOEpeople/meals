<?php

namespace App\Mealz\MealBundle\Twig\Extension;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishRepository;
use App\Mealz\MealBundle\Entity\Meal;
use Exception;
use Symfony\Component\Form\FormView;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * @TODO: CodeStyle, variable usage optimization (maybe use $dishes as attribute?)
 */
class Variation extends AbstractExtension
{
    /**
     * Dish consumption count period specified as date format used by PHP date() function.
     */
    private string $dishConsCountPeriod;
    private DishRepository $dishRepository;

    public function __construct(DishRepository $dishRepository, string $dishConsCountPeriod)
    {
        $this->dishRepository = $dishRepository;
        $this->dishConsCountPeriod = $dishConsCountPeriod;
    }

    /**
     * @return TwigFunction[]
     */
    public function getFunctions(): array
    {
        return [
            new TwigFunction('groupMeals', [$this, 'groupMeals']),
            new TwigFunction('groupMealsToArray', [$this, 'groupMealsToArray']),
            new TwigFunction('getFullTitleByDishAndVariation', [$this, 'getFullTitleByDishAndVariation']),
            new TwigFunction('getSortedVariation', [$this, 'getSortedVariation']),
            new TwigFunction('getDishCount', [$this, 'getDishCount']),
        ];
    }

    public function groupMeals(array $meals): array
    {
        $mealsArray = $mealsVariations = [];
        $combinedMeal = null;

        foreach ($meals as $meal) {
            /** @var Meal $meal */
            if (true === isset($meal->data) && (true === $meal->data instanceof Meal)) {
                $dish = $meal->data->getDish();
            } elseif ($meal instanceof Meal) {
                $dish = $meal->getDish();
            }

            if (false === is_null($dish) && (true === $dish->getParent() instanceof Dish)) {
                $parentId = $dish->getParent()->getId();
                $mealsVariations[$parentId][] = $meal;
            } else if ($dish->isCombinedDish())
                $combinedMeal = $meal;
            else {
                $mealsArray[] = $meal;
            }
        }

        return [
            'meals' => $mealsArray,
            'mealsVariations' => $mealsVariations,
            'combinedMeal' => $combinedMeal
        ];
    }

    /**
     * @param FormView $formViews
     */
    public function groupMealsToArray($formViews): array
    {
        $dishesGroupByParent = [];

        foreach ($formViews as $formView) {
            /** @var Meal $meal */
            $meal = $formView->vars['data'];
            $dish = $meal->getDish();
            if (null !== $dish && $dish->isEnabled()) {
                $parentDish = $dish->getParent();
                $dishId = (null === $parentDish) ? $dish->getId() : $parentDish->getId();
                $dishesGroupByParent[$dishId]['ids'][] = $dish->getId();
                $dishesGroupByParent[$dishId]['formViews'][] = $formView;
            }
        }

        return $dishesGroupByParent;
    }

    /**
     * @param int   $parentDishId
     * @param array $variations
     * @param array $dishes
     */
    public function getFullTitleByDishAndVariation($parentDishId, $variations, $dishes): string
    {
        $title = '';

        if ($parentDishId) {
            $title .= $this->getTitleForDish($parentDishId, $dishes);
        }

        if (is_array($variations) && !in_array($parentDishId, $variations)) {
            $title .= ' - ';
            foreach ($variations as $variationId) {
                if ($variationId !== $variations[0]) {
                    $title .= ', ';
                }
                $title .= $this->getTitleForDish($variationId, $dishes);
            }
        }

        return $title;
    }

    public function getSortedVariation($variations)
    {
        if (is_array($variations) && count($variations)) {
            uasort($variations, [$this, 'compareVariation']);
        }

        return $variations;
    }

    /**
     * Returns the name of the extension.
     */
    public function getName(): string
    {
        return 'variation';
    }

    /**
     * @throws Exception
     */
    public function getDishCount(Dish $dish): int
    {
        return $this->dishRepository->countNumberDishWasTaken($dish, $this->dishConsCountPeriod);
    }

    /**
     * @param $dishId
     * @param $dishList
     *
     * @return null
     */
    private function getTitleForDish($dishId, $dishList)
    {
        foreach ($dishList as $dish) {
            if ($dish->getId() === $dishId) {
                return $dish->getTitle();
            }
        }

        return null;
    }

    /**
     * @param array $first
     * @param array $second
     *
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     *
     * @see self::getSortedVariation
     */
    private function compareVariation($first, $second): int
    {
        $firstContent = strtolower($first['variations']['content']);
        $secondContent = strtolower($second['variations']['content']);
        if ($firstContent == $secondContent) {
            return 0;
        }

        return ($firstContent < $secondContent) ? -1 : 1;
    }
}
