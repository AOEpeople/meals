<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Entity;

use Override;

class NullDish extends Dish
{
    public function __construct()
    {
    }

    /**
     * @return null
     */
    #[Override]
    public function getParent(): ?Dish
    {
        return null;
    }

    /**
     * @psalm-return ''
     */
    #[Override]
    public function getSlug(): string
    {
        return '';
    }

    /**
     * @psalm-return ''
     */
    #[Override]
    public function getDescription(): ?string
    {
        return '';
    }

    #[Override]
    public function getPrice(): float
    {
        return 0.0;
    }

    /**
     * @psalm-return ''
     */
    #[Override]
    public function getTitle(): string
    {
        return '';
    }

    /**
     * @return true
     */
    #[Override]
    public function isEnabled(): bool
    {
        return true;
    }

    /**
     * @return false
     */
    #[Override]
    public function hasOneServingSize(): bool
    {
        return false;
    }

    /**
     * @psalm-return ''
     */
    #[Override]
    public function getCurrentLocale(): string
    {
        return '';
    }

    /**
     * @return null
     */
    #[Override]
    public function getDescriptionDe(): ?string
    {
        return null;
    }

    /**
     * @return null
     */
    #[Override]
    public function getDescriptionEn(): ?string
    {
        return null;
    }

    /**
     * @psalm-return ''
     */
    #[Override]
    public function getTitleDe(): string
    {
        return '';
    }

    /**
     * @psalm-return ''
     */
    #[Override]
    public function getTitleEn(): string
    {
        return '';
    }

    /**
     * @return null
     */
    #[Override]
    public function getCategory(): ?Category
    {
        return null;
    }

    #[Override]
    public function getVariations(): DishCollection
    {
        return new DishCollection();
    }

    /**
     * @return false
     */
    #[Override]
    public function hasVariations(): bool
    {
        return false;
    }

    /**
     * @return false
     */
    #[Override]
    public function isCombinedDish(): bool
    {
        return false;
    }
}
