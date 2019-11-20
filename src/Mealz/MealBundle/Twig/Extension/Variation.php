<?php


namespace Mealz\MealBundle\Twig\Extension;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Mealz\MealBundle\Entity\Dish;
use Mealz\MealBundle\Entity\Meal;
use Symfony\Component\VarDumper\VarDumper;
use Twig\TwigFunction;

/**
 * @TODO: CodeStyle, variable usage optimization (maybe use $dishes as attribute?)
 * Class Variation
 * @package Mealz\MealBundle\Twig\Extension
 */
class Variation extends \Twig_Extension
{
    protected $doctrine;

    protected $twig;

    /**
     * Constructor
     */
    public function __construct(RegistryInterface $doctrine, $twig)
    {
        $this->doctrine = $doctrine;
        $this->twig = $twig;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return array(
            new TwigFunction('groupMeals', [$this, 'groupMeals']),
            new TwigFunction('getDump', [$this, 'getDump']),
            new TwigFunction('groupMealsToArray', [$this, 'groupMealsToArray']),
            new TwigFunction('getFullTitleByDishAndVariation', [$this, 'getFullTitleByDishAndVariation']),
            new TwigFunction('getSortedVariation', [$this, 'getSortedVariation']),
            new TwigFunction('getDishCount', [$this, 'getDishCount']),
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
            if (isset($meal->data) === true && ($meal->data instanceof Meal === true)) {
                $dish = $meal->data->getDish();
            } elseif ($meal instanceof Meal) {
                $dish = $meal->getDish();
            }

            if (is_null($dish) === false && ($dish->getParent() instanceof Dish === true)) {
                $parentId = $dish->getParent()->getId();
                $mealsVariations[$parentId][] = $meal;
                $mealsVariationsCount[$parentId] = count($mealsVariations[$parentId]);
            } else {
                $mealsArray[] = $meal;
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
        $dishesGroupByParent = array();

        foreach ($formViews as $formView) {
            /** @var Meal $meal */
            $meal = $formView->vars['data'];
            $dish = $meal->getDish();
            if (null !== $dish) {
                $parentDish = $dish->getParent();
                $dishId = (null === $parentDish) ? $dish->getId() : $parentDish->getId();
                $dishesGroupByParent[$dishId]['ids'][] = $dish->getId();
                $dishesGroupByParent[$dishId]['formViews'][] = $formView;
            }
        }

        return $dishesGroupByParent;
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

    public function getSortedVariation($variations)
    {
        if (is_array($variations) && count($variations)) {
            uasort($variations, array($this, 'compareVariation'));
        }
        return $variations;
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
     * Returns the name of the extension.
     *
     * @return string The extension name
     */
    public function getDishCount($dish)
    {
        $entityManager = $this->doctrine->getManager();
        $dishRepo = $entityManager->getRepository('MealzMealBundle:Dish');
        return $dishRepo->countNumberDishWasTaken($dish, $this->twig->getGlobals()['countDishPeriod']);
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

    /**
     * @see self::getSortedVariation
     * 
     * @param array $first
     * @param array $second
     * 
     * @return int
     * 
     * @SuppressWarnings(PHPMD.UnusedPrivateMethod)
     */
    private function compareVariation($first, $second)
    {
        $firstContent = strtolower($first['variations']['content']);
        $secondContent = strtolower($second['variations']['content']);
        if ($firstContent == $secondContent) {
            return 0;
        }
        return ($firstContent < $secondContent) ? -1 : 1;
    }
}
