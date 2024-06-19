<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Repository\CategoryRepository;
use App\Mealz\MealBundle\Repository\DishRepository;
use Exception;

class DishService
{
    /**
     * Number of times a dish must be taken before it is considered old.
     */
    private int $newFlagThreshold;

    /**
     * Period of time in which the number of times a dish was taken is counted.
     */
    private string $dishConsCountPeriod;
    private ApiService $apiService;
    private CategoryRepository $categoryRepository;
    private DishRepository $dishRepository;

    public function __construct(
        int $newFlagThreshold,
        string $dishConsCountPeriod,
        ApiService $apiService,
        CategoryRepository $categoryRepository,
        DishRepository $dishRepository
    ) {
        $this->newFlagThreshold = $newFlagThreshold;
        $this->dishConsCountPeriod = $dishConsCountPeriod;
        $this->apiService = $apiService;
        $this->categoryRepository = $categoryRepository;
        $this->dishRepository = $dishRepository;
    }

    /**
     * @throws Exception
     */
    public function isNew(Dish $dish): bool
    {
        if (true === $dish->isCombinedDish()) {
            return false;
        }

        return $this->dishRepository->countNumberDishWasTaken($dish, '0000-01-01') < $this->newFlagThreshold;
    }

    /**
     * Updates a Dish with a given set of parameters.
     */
    public function updateHelper(Dish $dish, array $parameters): void
    {
        if (true === $this->apiService->isParamValid($parameters, 'titleDe', 'string')) {
            $dish->setTitleDe($parameters['titleDe']);
        }
        if (true === $this->apiService->isParamValid($parameters, 'titleEn', 'string')) {
            $dish->setTitleEn($parameters['titleEn']);
        }
        if (true === $this->apiService->isParamValid($parameters, 'oneServingSize', 'boolean')) {
            $dish->setOneServingSize($parameters['oneServingSize']);
            if (true === $dish->hasVariations()) {
                /** @var Dish $variation */
                foreach ($dish->getVariations() as $variation) {
                    $variation->setOneServingSize($parameters['oneServingSize']);
                }
            }
        }
        if (true === $this->apiService->isParamValid($parameters, 'descriptionDe', 'string')) {
            $dish->setDescriptionDe($parameters['descriptionDe']);
        }
        if (true === $this->apiService->isParamValid($parameters, 'descriptionEn', 'string')) {
            $dish->setDescriptionEn($parameters['descriptionEn']);
        }
        if (true === $this->apiService->isParamValid($parameters, 'category', 'integer')) {
            $dish->setCategory($this->categoryRepository->find($parameters['category']));
        }
    }

    public function getDishCount(): array
    {
        $arr = [];
        try {
            $arr = $this->dishRepository->countNumberDishesWereTaken($this->dishConsCountPeriod);
        } catch (Exception $e) {
            throw new Exception('203: Error in count: ' . $e->getMessage());
        }

        return $arr;
    }

    public function getUniqueDishesFromMeals(array $meals): array
    {
        $uniqueMeals = [];
        /** @var Meal $meal */
        foreach ($meals as $meal) {
            if (false === ($meal->getDish() instanceof DishVariation)) {
                $uniqueMeals[] = $meal->getDish();
            } else {
                $uniqueMeals[] = $meal->getDish()->getParent();
            }
        }

        return $uniqueMeals;
    }
}
