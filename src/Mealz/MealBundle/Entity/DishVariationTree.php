<?php

namespace App\Mealz\MealBundle\Entity;

class DishVariationTree
{
    private Dish $parentDish;
    private array $variationNodes;

    public function __construct(Dish $dish) {
        if($dish instanceof DishVariation) {
            $this->parentDish = $dish->getParent();
            $this->variationNodes = [$dish];
        } else {
            $this->parentDish = $dish;
            $this->variationNodes = [];
        }
    }

    public function getParentDish(): Dish
    {
        return $this->parentDish;
    }

    public function setParentDish(?Dish $parentDish): void
    {
        $this->parentDish = $parentDish;
    }

    public function getVariationNodes(): array
    {
        return $this->variationNodes;
    }

    public function addVariation(DishVariation $variation): void
    {
        if (!in_array($variation, $this->variationNodes, true)) {
            $this->variationNodes[] = $variation;
        }
    }

    public function __toString(): string
    {
        $out = $this->parentDish->getTitleEn();
        if (count($this->variationNodes) > 0) {
            $out .= ' (' . implode(', ', $this->variationNodes) . ')';
        }

        return $out;
    }
}