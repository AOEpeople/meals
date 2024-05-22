<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

class NullDish extends Dish
{
    public function __construct()
    {
    }

    /**
     * @return null
     */
    public function getParent(): ?Dish
    {
        return null;
    }

    /**
     * @psalm-return ''
     */
    public function getSlug(): string
    {
        return '';
    }

    /**
     * @psalm-return ''
     */
    public function getDescription(): ?string
    {
        return '';
    }

    public function getPrice(): float
    {
        return 0.0;
    }

    /**
     * @psalm-return ''
     */
    public function getTitle(): string
    {
        return '';
    }

    /**
     * @return true
     */
    public function isEnabled(): bool
    {
        return true;
    }

    /**
     * @return false
     */
    public function hasOneServingSize(): bool
    {
        return false;
    }

    /**
     * @psalm-return ''
     */
    public function getCurrentLocale(): string
    {
        return '';
    }

    /**
     * @return null
     */
    public function getDescriptionDe(): ?string
    {
        return null;
    }

    /**
     * @return null
     */
    public function getDescriptionEn(): ?string
    {
        return null;
    }

    /**
     * @psalm-return ''
     */
    public function getTitleDe(): string
    {
        return '';
    }

    /**
     * @psalm-return ''
     */
    public function getTitleEn(): string
    {
        return '';
    }

    /**
     * @return null
     */
    public function getCategory(): ?Category
    {
        return null;
    }

    public function getVariations(): DishCollection
    {
        return new DishCollection();
    }

    /**
     * @return false
     */
    public function hasVariations(): bool
    {
        return false;
    }

    /**
     * @return false
     */
    public function isCombinedDish(): bool
    {
        return false;
    }
}
