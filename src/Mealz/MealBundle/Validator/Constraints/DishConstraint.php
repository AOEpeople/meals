<?php

namespace App\Mealz\MealBundle\Validator\Constraints;

use Override;
use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
final class DishConstraint extends Constraint
{
    public string $message = 'error.meal.has_participants';

    #[Override]
    public function getTargets(): string|array
    {
        return self::CLASS_CONSTRAINT;
    }
}
