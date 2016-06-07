<?php

namespace Mealz\MealBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DishConstraint extends Constraint
{
    public $message = "There are already participants for '%string%' ";

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}