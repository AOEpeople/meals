<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Model representing a Dish variation.
 *
 * @ORM\Entity(repositoryClass="DishRepository")
 *
 * @author Chetan Thapliyal <chetan.thapliyal@aoe.com>
 */
class DishVariation extends Dish
{
}
