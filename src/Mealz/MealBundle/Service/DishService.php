<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Repository\CategoryRepository;
use App\Mealz\MealBundle\Repository\DishRepository;
use Exception;

class DishService
{
    /**
     * Number of times a dish must be taken before it is considered old.
     */
    private int $newFlagThreshold;

    private ApiService $apiService;
    private CategoryRepository $categoryRepository;
    private DishRepository $dishRepository;

    public function __construct(
        int $newFlagThreshold,
        ApiService $apiService,
        CategoryRepository $categoryRepository,
        DishRepository $dishRepository
    ) {
        $this->newFlagThreshold = $newFlagThreshold;
        $this->apiService = $apiService;
        $this->categoryRepository = $categoryRepository;
        $this->dishRepository = $dishRepository;
    }

    /**
     * @throws Exception
     */
    public function isNew(Dish $dish): bool
    {
        if ($dish->isCombinedDish()) {
            return false;
        }

        return $this->dishRepository->countNumberDishWasTaken($dish, '0000-01-01') < $this->newFlagThreshold;
    }

    /**
     * Updates a Dish with a given set of parameters.
     */
    public function updateHelper(Dish $dish, array $parameters): void
    {
        if ($this->apiService->isParamValid($parameters, 'titleDe', 'string')) {
            $dish->setTitleDe($parameters['titleDe']);
        }
        if ($this->apiService->isParamValid($parameters, 'titleEn', 'string')) {
            $dish->setTitleEn($parameters['titleEn']);
        }
        if ($this->apiService->isParamValid($parameters, 'oneServingSize', 'boolean')) {
            $dish->setOneServingSize($parameters['oneServingSize']);
            if ($dish->hasVariations()) {
                /** @var Dish $variation */
                foreach ($dish->getVariations() as $variation) {
                    $variation->setOneServingSize($parameters['oneServingSize']);
                }
            }
        }
        if ($this->apiService->isParamValid($parameters, 'descriptionDe', 'string')) {
            $dish->setDescriptionDe($parameters['descriptionDe']);
        }
        if ($this->apiService->isParamValid($parameters, 'descriptionEn', 'string')) {
            $dish->setDescriptionEn($parameters['descriptionEn']);
        }
        if ($this->apiService->isParamValid($parameters, 'category', 'integer')) {
            $dish->setCategory($this->categoryRepository->find($parameters['category']));
        }
    }
}
