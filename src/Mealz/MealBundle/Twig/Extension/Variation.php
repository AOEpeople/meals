<?php


namespace Mealz\MealBundle\Twig\Extension;

use Doctrine\ORM\Mapping\Entity;
use Mealz\MealBundle\Entity\Dish;
use Mealz\MealBundle\Entity\DishVariation;
use Mealz\MealBundle\Entity\Meal;
use Symfony\Component\Form\FormView;
use Symfony\Component\VarDumper\VarDumper;

class Variation extends \Twig_Extension
{
    protected $formMeals;

	public function getFunctions()
	{
		return array(
			'groupMeals' => new \Twig_Function_Method($this, 'groupMeals'),
			'isVariation' => new \Twig_Function_Method($this, 'isVariation'),
			'groupMealsToParents' => new \Twig_Function_Method($this, 'groupMealsToParents'),
			'getDump' => new \Twig_Function_Method($this, 'getDump'),
			'getGroupedDishes' => new \Twig_Function_Method($this, 'getGroupedDishes'),
			'getDishesFromGroup' => new \Twig_Function_Method($this, 'getDishesFromGroup'),
			'getVariationsForDish' => new \Twig_Function_Method($this, 'getVariationsForDish'),
			'getDishesWithinMeals' => new \Twig_Function_Method($this, 'getDishesWithinMeals'),
			'groupMealsToArray' => new \Twig_Function_Method($this, 'groupMealsToArray'),
			'getFullTitleByDishAndVariation' => new \Twig_Function_Method($this, 'getFullTitleByDishAndVariation'),
			'getGroupedFormViews' => new \Twig_Function_Method($this, 'getGroupedFormViews'),
			'removeUsedFormViews' => new \Twig_Function_Method($this, 'removeUsedFormViews'),
			'setFormMeals' => new \Twig_Function_Method($this, 'setFormMeals'),
			'reloadFormMeals' => new \Twig_Function_Method($this, 'reloadFormMeals'),
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
	 * Input an array of meals for a certain day. This method identifies the dishes attached to the meals.
	 * When the found dish is a dishvariation the method finds the related dish for the variation and returns
	 * the dish instead.
	 *
	 * @param $meals    Array of meals
	 * @return array    Array of dishes found. Duplicates removed
	 */
	public function getDishesWithinMeals($meals)
	{
		$result = array();
		foreach ($meals as $meal) {
			if($meal['dish']->vars['data'] instanceof \Mealz\MealBundle\Entity\DishVariation){
				$result[$meal['dish']->vars['data']->getParent()->getId()]['object'] = $meal['dish']->vars['data']->getParent();
				$result[$meal['dish']->vars['data']->getParent()->getId()]['selectedVariations'][] = $meal['dish']->vars['data'];
			} elseif ($meal['dish']->vars['data'] instanceof \Mealz\MealBundle\Entity\DishVariation){
				$result[$meal['dish']->vars['data']->getId()] = $meal['dish']->vars['data'];
			}
		}
		return $result;
	}

	public function getGroupedDishes($meal)
	{
		$groupedDishes = array();
		foreach ($meal->children['dish']->vars['choices'] as $dishGroup)
		{
			if (isset($dishGroup->choices))
			{
				array_push($groupedDishes, $dishGroup);
			}
		}

		return $groupedDishes;
	}

	public function getDishesFromGroup($dishGroup)
	{
		$dishes = array();
		foreach ($dishGroup->choices as $dish) {
			array_push($dishes, $dish);
		}
		return $dishes;
	}

	public function getVariationsForDish($dish)
	{
		$variations = array();
		foreach ($dish->data->getVariations()->getValues() as $variation) {
			array_push($variations, $variation);
		}
		return $variations;
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

    public function groupMealsToArray($meals)
    {
        $selectedMeals = array();
        foreach ($meals as $meal) {
            /** @var Meal $meal */
            $dish = $meal->getDish();
            if (null !== $dish) {
                $parentDish = $dish->getParent();
                $dishId = (null === $parentDish) ? $dish->getId() : $parentDish->getId();
                $selectedMeals[$dishId][] = $dish->getId();
            }
        }

//        VarDumper::dump($mealArray);die();
        return $selectedMeals;
	}

    public function getFullTitleByDishAndVariation($parentDishId, $variations, $dishes)
    {
        $title = '';

        if ($parentDishId) {
            $title .= $this->getTitleForDish($parentDishId, $dishes);
        }

        if ($variations) {
            foreach ($variations as $variationId) {
                $title .= ' '.$this->getTitleForDish($variationId, $dishes);
            }
        }

        return $title;
	}

    public function getGroupedFormViews($selectedDishes, $formViews)
    {
        $resultFormViews = array();

        if (null === $selectedDishes) return $resultFormViews;

        foreach ($formViews as $formView) {
            /** @var Meal $meal */
            $meal = $formView->vars['value'];
            $dish = $meal->getDish();
            if (null !== $dish && in_array($dish->getId(), $selectedDishes)) {
                $resultFormViews[] = $formView;
            }
        }
        return $resultFormViews;
	}

    public function removeUsedFormViews($formMeals, $formViews)
    {
        $diff = array_udiff(
            $formMeals,
            $formViews,
            function ($a, $b) {
                $aId = $a->vars['id'];
                $bId = $b->vars['id'];

                return ($aId === $bId) ? 0 : -1;
            }
        );
        $this->formMeals = $diff;
    }

    public function setFormMeals($formMeals)
    {
        $this->formMeals = $formMeals;
    }

    public function reloadFormMeals()
    {
        return $this->formMeals;
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
