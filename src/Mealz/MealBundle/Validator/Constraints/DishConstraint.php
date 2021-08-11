<?php

namespace App\Mealz\MealBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DishConstraint extends Constraint
{
    public $message = "error.meal.has_participants";

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
