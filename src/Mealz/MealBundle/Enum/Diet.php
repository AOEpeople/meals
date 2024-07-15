<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Enum;

enum Diet: string
{
    case VEGAN = 'vegan';
    case VEGETARIAN = 'vegetarian';
    case MEAT = 'meat';
}