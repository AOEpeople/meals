<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Enum\Diet;
use App\Mealz\MealBundle\Event\WeekUpdateEvent;
use App\Mealz\MealBundle\Repository\CategoryRepository;
use App\Mealz\MealBundle\Repository\DishRepository;
use App\Mealz\MealBundle\Repository\MealRepositoryInterface;
use Exception;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
    private EventDispatcherInterface $eventDispatcher;
    private MealRepositoryInterface $mealRepo;

    public function __construct(
        int $newFlagThreshold,
        string $dishConsCountPeriod,
        ApiService $apiService,
        CategoryRepository $categoryRepository,
        DishRepository $dishRepository,
        EventDispatcherInterface $eventDispatcher,
        MealRepositoryInterface $mealRepo,
    ) {
        $this->newFlagThreshold = $newFlagThreshold;
        $this->dishConsCountPeriod = $dishConsCountPeriod;
        $this->apiService = $apiService;
        $this->categoryRepository = $categoryRepository;
        $this->dishRepository = $dishRepository;
        $this->eventDispatcher = $eventDispatcher;
        $this->mealRepo = $mealRepo;
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
        $this->setServingSizeIfValid($dish, $parameters);
        $this->setTitleIfValid($dish, $parameters);
        $this->setDescriptionIfValid($dish, $parameters);
        $this->setCategoryIfValid($dish, $parameters);
        $this->setDietIfValid($dish, $parameters);
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

    public function updateCombisForDish(Dish $dish): void
    {
        $meals = $this->mealRepo->getFutureMealsForDish($dish);

        if (true === $dish->hasVariations()) {
            foreach ($dish->getVariations() as $variation) {
                $res = $this->mealRepo->getFutureMealsForDish($variation);
                $meals = array_merge($meals, $res);
            }
        }

        $weeksToUpdate = array_map(
            fn ($meal) => $meal->getDay()->getWeek(),
            array_unique($meals)
        );
        $uniqueWeeks = array_unique($weeksToUpdate);

        foreach ($uniqueWeeks as $week) {
            $this->eventDispatcher->dispatch(new WeekUpdateEvent($week, false));
        }
    }

    private function setDietIfValid(Dish $dish, array $parameters): void
    {
        if (true === $this->apiService->isParamValid($parameters, 'diet', 'string')) {
            $dish->setDiet(Diet::tryFrom($parameters['diet']));
        }
    }

    private function setCategoryIfValid(Dish $dish, array $parameters): void
    {
        if (true === $this->apiService->isParamValid($parameters, 'category', 'integer')) {
            $dish->setCategory($this->categoryRepository->find($parameters['category']));
        }
    }

    private function setDescriptionIfValid(Dish $dish, array $parameters): void
    {
        if (true === $this->apiService->isParamValid($parameters, 'descriptionDe', 'string')) {
            $dish->setDescriptionDe($parameters['descriptionDe']);
        }
        if (true === $this->apiService->isParamValid($parameters, 'descriptionEn', 'string')) {
            $dish->setDescriptionEn($parameters['descriptionEn']);
        }
    }

    private function setServingSizeIfValid(Dish $dish, array $parameters): void
    {
        if (true === $this->apiService->isParamValid($parameters, 'oneServingSize', 'boolean')) {
            if (true === $parameters['oneServingSize'] && true === $this->hasBookedCombiMealsInFuture($dish)) {
                throw new Exception('204: The servingSize cannot be adjusted, because there are booked combi-meals');
            }

            $dish->setOneServingSize($parameters['oneServingSize']);
            if (true === $dish->hasVariations()) {
                /** @var Dish $variation */
                foreach ($dish->getVariations() as $variation) {
                    $variation->setOneServingSize($parameters['oneServingSize']);
                }
            }
        }
    }

    private function setTitleIfValid(Dish $dish, array $parameters): void
    {
        if (true === $this->apiService->isParamValid($parameters, 'titleDe', 'string')) {
            $dish->setTitleDe($parameters['titleDe']);
        }
        if (true === $this->apiService->isParamValid($parameters, 'titleEn', 'string')) {
            $dish->setTitleEn($parameters['titleEn']);
        }
    }

    private function hasBookedCombiMealsInFuture(Dish $dish): bool
    {
        return $this->dishRepository->hasDishAssociatedCombiMealsInFuture($dish);
    }
}
