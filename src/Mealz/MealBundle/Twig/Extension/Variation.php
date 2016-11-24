<?php


namespace Mealz\MealBundle\Twig\Extension;

use Mealz\MealBundle\Entity\Meal;
use Symfony\Component\VarDumper\VarDumper;

/**
 * @TODO: CodeStyle, variable usage optimization (maybe use $dishes as attribute?)
 * Class Variation
 * @package Mealz\MealBundle\Twig\Extension
 */
class Variation extends \Twig_Extension
{
    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            'groupMeals' => new \Twig_Function_Method($this, 'groupMeals'),
            'getDump' => new \Twig_Function_Method($this, 'getDump'),
            'groupMealsToArray' => new \Twig_Function_Method($this, 'groupMealsToArray'),
            'getFullTitleByDishAndVariation' => new \Twig_Function_Method($this, 'getFullTitleByDishAndVariation'),
        );
    }

    /**
     * Group the meals
     * @param array $meals
     * @return array
     */
    public function groupMeals($meals)
    {
        $mealsArray = $mealsVariations = array();
        $mealsVariationsCount = array();
        foreach ($meals as $meal) {
            /** @var Meal $meal */
            $dish = $meal->getDish();
            if ($dish->getParent().is_object() && $dish->getParent()) {
                $parentId = $dish->getParent()->getId();
                $mealsVariations[$parentId][] = $meal;
                $mealsVariationsCount[$parentId] = count($mealsVariations[$parentId]);
            } else {
                $mealsArray[] = $meal;
            }
        }

        foreach ($mealsVariationsCount as $id => $count) {
            if ($count === 1) {
                $mealsArray[] = $mealsVariations[$id][0];
                unset($mealsVariations[$id]);
            }
        }

        return array(
            'meals' => $mealsArray,
            'mealsVariations' => $mealsVariations,
        );
    }

    /**
     * @TODO: move this function inside the TemplateBundle into some more generic Twig Extension (Base.php or something like that)
     * @param array $dump
     */
    public function getDump($dump)
    {
        VarDumper::dump($dump);
    }

    /**
     * Group the Meals to an Array
     * @param FormView $formViews
     * @return array
     */
    public function groupMealsToArray($formViews)
    {
        $dishesGroupedByParent = array();

        foreach ($formViews as $formView) {
            /** @var Meal $meal */
            $meal = $formView->vars['data'];
            $dish = $meal->getDish();
            if (null !== $dish) {
                $parentDish = $dish->getParent();
                $dishId = (null === $parentDish) ? $dish->getId() : $parentDish->getId();
                $dishesGroupedByParent[$dishId]['ids'][] = $dish->getId();
                $dishesGroupedByParent[$dishId]['formViews'][] = $formView;
            }
        }

        return $dishesGroupedByParent;
    }

    /**
     * @param integer $parentDishId
     * @param array $variations
     * @param array $dishes
     * @return string
     */
    public function getFullTitleByDishAndVariation($parentDishId, $variations, $dishes)
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

    /**
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'variation';
    }

    /**
     * @param $dishId
     * @param $dishList
     * @return null
     */
    private function getTitleForDish($dishId, $dishList)
    {
        foreach ($dishList as $key => $dish) {
            if ($dish->getId() === $dishId) {
                return $dishList[$key]->getTitle();
            }
        }

        return null;
    }
}
