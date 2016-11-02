<?php


namespace Mealz\MealBundle\Twig\Extension;

use Mealz\MealBundle\Entity\Meal;

class Variation extends \Twig_Extension
{

    public function getFunctions()
    {
        return array(
            'groupMeals' => new \Twig_Function_Method($this, 'groupMeals'),
        );
    }

    public function groupMeals($meals)
    {
        $mealsArray = $mealsVariations = array();
        $mealsVariationsCount = array();
        foreach ($meals as $meal) {
            /** @var Meal $meal */
            $dish = $meal->getDish();
            if ($dish->getParent()) {
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
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getName()
    {
        return 'variation';
    }
}