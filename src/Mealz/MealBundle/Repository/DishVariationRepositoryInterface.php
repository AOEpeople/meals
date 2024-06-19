<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\DishVariation;
use Doctrine\Persistence\ObjectRepository;

/**
 * @template-extends ObjectRepository<DishVariation>
 */
interface DishVariationRepositoryInterface extends ObjectRepository
{
}
