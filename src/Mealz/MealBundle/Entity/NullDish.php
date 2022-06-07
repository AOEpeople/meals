<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

class NullDish extends Dish
{
    public function __construct()
    {
    }

    public function getParent(): ?Dish
    {
        return null;
    }

    public function getSlug(): string
    {
        return '';
    }

    public function getDescription(): ?string
    {
        return '';
    }

    public function getPrice(): float
    {
        return 0.0;
    }

    public function getTitle(): string
    {
        return '';
    }

    public function isEnabled(): bool
    {
        return true;
    }

    public function hasOneServingSize(): bool
    {
        return false;
    }

    public function getCurrentLocale(): string
    {
        return '';
    }

    public function getDescriptionDe(): ?string
    {
        return null;
    }

    public function getDescriptionEn(): ?string
    {
        return null;
    }

    public function getTitleDe(): string
    {
        return '';
    }

    public function getTitleEn(): string
    {
        return '';
    }

    public function getCategory(): ?Category
    {
        return null;
    }

    public function getVariations(): DishCollection
    {
        return new DishCollection();
    }

    public function hasVariations(): bool
    {
        return false;
    }

    public function isCombinedDish(): bool
    {
        return false;
    }
}
