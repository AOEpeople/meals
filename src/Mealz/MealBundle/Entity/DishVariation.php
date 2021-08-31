<?php

namespace App\Mealz\MealBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Model representing a Dish variation.
 *
 * @ORM\Entity(repositoryClass="DishRepository")
 */
class DishVariation extends Dish
{
}
