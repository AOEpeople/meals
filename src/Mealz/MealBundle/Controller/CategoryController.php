<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Repository\CategoryRepositoryInterface;

class CategoryController extends BaseListController
{
    public function __construct(CategoryRepositoryInterface $repository)
    {
        $this->setRepository($repository);
        $this->setEntityName('Category');
    }
}
