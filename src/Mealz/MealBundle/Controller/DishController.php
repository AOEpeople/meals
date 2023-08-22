<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Repository\CategoryRepository;
use App\Mealz\MealBundle\Repository\CategoryRepositoryInterface;
use App\Mealz\MealBundle\Repository\DishRepository;
use App\Mealz\MealBundle\Service\ApiService;
use App\Mealz\MealBundle\Service\DishService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("is_granted('ROLE_KITCHEN_STAFF')")
 */
class DishController extends BaseListController
{
    private float $defaultPrice;
    private CategoryRepositoryInterface $categoryRepository;
    private DishRepository $dishRepository;
    private EntityManagerInterface $em;
    private DishService $dishService;
    private ApiService $apiService;
    private LoggerInterface $logger;

    public function __construct(
        float $price,
        ApiService $apiService,
        CategoryRepository $categoryRepository,
        DishRepository $dishRepository,
        DishService $dishService,
        EntityManagerInterface $em,
        LoggerInterface $logger
    ) {
        $this->defaultPrice = $price;
        $this->apiService = $apiService;
        $this->categoryRepository = $categoryRepository;
        $this->dishRepository = $dishRepository;
        $this->dishService = $dishService;
        $this->em = $em;
        $this->logger = $logger;
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

        return new JsonResponse($dishes, 200);
    }

    /**
     * Creates a new dish.
     */
    public function new(Request $request): JsonResponse
    {
        try {
            $parameters = json_decode($request->getContent(), true);
            if (
                false === isset($parameters['titleDe']) ||
                false === isset($parameters['titleEn']) ||
                false === isset($parameters['oneServingSize'])
            ) {
                throw new Exception('201: Missing parameters');
            }

            $dish = new Dish();
            $dish->setTitleDe($parameters['titleDe']);
            $dish->setTitleEn($parameters['titleEn']);
            $dish->setOneServingSize($parameters['oneServingSize']);
            $dish->setPrice($this->defaultPrice);

            if (true === $this->apiService->isParamValid($parameters, 'descriptionDe', 'string')) {
                $dish->setDescriptionDe($parameters['descriptionDe']);
            }
            if (true === $this->apiService->isParamValid($parameters, 'descriptionEn', 'string')) {
                $dish->setDescriptionEn($parameters['descriptionEn']);
            }
            if (true === $this->apiService->isParamValid($parameters, 'category', 'integer')) {
                $dish->setCategory($this->categoryRepository->find($parameters['category']));
            }

            $this->em->persist($dish);
            $this->em->flush();

            return new JsonResponse(null, 200);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['message' => $e->getMessage()], 500);
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

            return new JsonResponse(null, 200);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Updates a dish.
     */
    public function update(Dish $dish, Request $request): JsonResponse
    {
        try {
            $parameters = json_decode($request->getContent(), true);

            $this->dishService->updateHelper($dish, $parameters);

            $this->em->persist($dish);
            $this->em->flush();

            return new JsonResponse($dish, 200);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['message' => $e->getMessage()], 500);
        }
    }
}
