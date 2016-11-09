<?php


namespace Mealz\MealBundle\Twig\Extension;

use Mealz\MealBundle\Entity\Meal;
use Symfony\Component\VarDumper\VarDumper;

class Variation extends \Twig_Extension
{

    public function getFunctions()
    {
        return array(
            'groupMeals' => new \Twig_Function_Method($this, 'groupMeals'),
            'isVariation' => new \Twig_Function_Method($this, 'isVariation'),
            'groupMealsToParents' => new \Twig_Function_Method($this, 'groupMealsToParents'),
            'getDump' => new \Twig_Function_Method($this, 'getDump'),
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

    public function getDump($dump){
        VarDumper::dump($dump);
    }
    /**
     * Group Meals to Parents
     * @param $meals
     * @return array
     */
    public function groupMealsToParents($meals)
    {
        $dishes = array();

        foreach ($meals as $meal) {
            if($meal->vars['value']->getId() !== null){
                /** @var Meal $dish */
                $dish = $meal->vars['value']->getDish();
                if ($dish->getParent() !== null) {

                    $dishes[] = $dish->getParent();
                } else {
                    $dishes[] =  $dish;
                }
            }
        }

        $mealsArray = $this->getMealsSorted(array_unique($dishes));
        return $mealsArray;
    }

    /**
     * @param $dishes
     * @return array
     */
    public function getMealsSorted($dishes)
    {
        $sortedMeals = array();

        foreach($dishes as $dish){
            $sortedMeals[$dish->getCategory()->getTitle()][] = $dish;
        }

        return $sortedMeals;
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