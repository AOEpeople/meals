<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Category;
use Doctrine\Persistence\ObjectRepository;

/**
 * @template-extends ObjectRepository<Category>
 */
interface CategoryRepositoryInterface extends ObjectRepository
{
}
