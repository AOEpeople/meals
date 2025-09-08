<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\DishVariation;

/**
 * @extends BaseRepository<int, DishVariation>
 */
final class DishVariationRepository extends BaseRepository implements DishVariationRepositoryInterface
{
}
