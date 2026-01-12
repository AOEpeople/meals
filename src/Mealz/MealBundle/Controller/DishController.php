<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishCollection;
use App\Mealz\MealBundle\Enum\Diet;
use App\Mealz\MealBundle\Repository\CategoryRepositoryInterface;
use App\Mealz\MealBundle\Repository\DishRepositoryInterface;
use App\Mealz\MealBundle\Service\ApiService;
use App\Mealz\MealBundle\Service\DishService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_KITCHEN_STAFF')]
final class DishController extends BaseListController
{
    public function __construct(
        private readonly ApiService $apiService,
        private readonly CategoryRepositoryInterface $categoryRepository,
        private readonly DishRepositoryInterface $dishRepository,
        private readonly DishService $dishService,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
    ) {
    }

    /**
     * Returns a list of all dishes and their variations.
     */
    public function getDishes(): JsonResponse
    {
        $dishes = $this->dishRepository->findBy(['parent' => null, 'enabled' => true]);
        $combiDish = $this->dishRepository->findBy(['slug' => 'combined-dish']);

        if (null !== $combiDish && 0 < count($combiDish)) {
            $dishes[] = $combiDish[0];
        }

        /** @var Dish $dish */
        foreach ($dishes as $dish) {
            if (count($dish->getVariations()) > 0) {
                $variations = array_values(array_filter(
                    $dish->getVariations()->toArray(),
                    fn ($variation) => $variation->isEnabled()
                ));
                $dish->setVariations(new DishCollection($variations));
            }
        }

        return new JsonResponse($dishes, Response::HTTP_OK);
    }

    /**
     * Creates a new dish.
     */
    public function new(Request $request): JsonResponse
    {
        try {
            $parameters = json_decode($request->getContent(), true);
            if (
                false === isset($parameters['titleDe'])
                || false === isset($parameters['titleEn'])
                || false === isset($parameters['oneServingSize'])
            ) {
                throw new Exception('201: Missing parameters');
            }

            $dish = new Dish();
            $dish->setTitleDe($parameters['titleDe']);
            $dish->setTitleEn($parameters['titleEn']);
            $dish->setOneServingSize($parameters['oneServingSize']);

            if (true === $this->apiService->isParamValid($parameters, 'descriptionDe', 'string')) {
                $dish->setDescriptionDe($parameters['descriptionDe']);
            }
            if (true === $this->apiService->isParamValid($parameters, 'descriptionEn', 'string')) {
                $dish->setDescriptionEn($parameters['descriptionEn']);
            }
            if (true === $this->apiService->isParamValid($parameters, 'category', 'integer')) {
                $dish->setCategory($this->categoryRepository->find($parameters['category']));
            }
            if (true === $this->apiService->isParamValid($parameters, 'diet', 'string')) {
                $dish->setDiet(Diet::tryFrom($parameters['diet']));
            }

            $this->em->persist($dish);
            $this->em->flush();

            return new JsonResponse(null, Response::HTTP_OK);
        } catch (Exception $e) {
            $this->logger->info('dish create error', $this->getTrace($e));

            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Deletes a dish.
     */
    public function delete(Dish $dish): JsonResponse
    {
        try {
            // hide the dish if it has been assigned to a meal, else delete it
            if (true === $this->dishRepository->hasDishAssociatedMeals($dish)) {
                $dish->setEnabled(false);
                $this->em->persist($dish);
            } else {
                $this->em->remove($dish);
            }
            $this->em->flush();

            return new JsonResponse(null, Response::HTTP_OK);
        } catch (Exception $e) {
            $this->logger->info('dish delete error', $this->getTrace($e));

            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Updates a dish.
     */
    public function update(Dish $dish, Request $request): JsonResponse
    {
        try {
            $parameters = json_decode($request->getContent(), true);
            $oneServingSize = $dish->hasOneServingSize();

            $this->dishService->updateHelper($dish, $parameters);

            $this->em->persist($dish);
            $this->em->flush();

            if (true === isset($parameters['oneServingSize']) && $oneServingSize !== $parameters['oneServingSize']) {
                $this->dishService->updateCombisForDish($dish);
            }

            return new JsonResponse($dish, Response::HTTP_OK);
        } catch (Exception $e) {
            $this->logger->info('dish update error', $this->getTrace($e));

            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
