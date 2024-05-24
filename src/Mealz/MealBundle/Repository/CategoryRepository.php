<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Category;

/**
 * @extends BaseRepository<int, Category>
 */
class CategoryRepository extends BaseRepository implements CategoryRepositoryInterface
{
}
