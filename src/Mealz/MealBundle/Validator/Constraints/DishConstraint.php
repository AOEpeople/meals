<?php

namespace App\Mealz\MealBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class DishConstraint extends Constraint
{
    public string $message = 'error.meal.has_participants';

    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
