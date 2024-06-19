<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Slot;
use Doctrine\Persistence\ObjectRepository;

/**
 * @template-extends ObjectRepository<Slot>
 */
interface SlotRepositoryInterface extends ObjectRepository
{
}
