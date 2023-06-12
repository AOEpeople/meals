<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Repository\CategoryRepository;
use App\Mealz\MealBundle\Repository\CategoryRepositoryInterface;
use App\Mealz\MealBundle\Repository\DishRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
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

    public function __construct(
        float $price,
        CategoryRepository $categoryRepository,
        DishRepository $dishRepository,
        EntityManagerInterface $em
    ) {
        $this->defaultPrice = $price;
        $this->categoryRepository = $categoryRepository;
        $this->dishRepository = $dishRepository;
        $this->em = $em;
    }

    public function getDishes(): JsonResponse
    {
        $dishes = $this->dishRepository->findBy(['parent' => null, 'enabled' => true]);

        return new JsonResponse($dishes, 200);
    }

    public function new(Request $request): JsonResponse
    {
        try {
            $parameters = json_decode($request->getContent(), true);
            if (!isset($parameters['titleDe']) || !isset($parameters['titleEn']) || !isset($parameters['oneServingSize'])) {
                throw new Exception('Missing parameters');
            }

            $dish = new Dish();
            $dish->setTitleDe($parameters['titleDe']);
            $dish->setTitleEn($parameters['titleEn']);
            $dish->setOneServingSize($parameters['oneServingSize']);
            $dish->setPrice($this->defaultPrice);

            if ($this->isParamValid($parameters, 'descriptionDe', 'string')) {
                $dish->setDescriptionDe($parameters['descriptionDe']);
            }
            if ($this->isParamValid($parameters, 'descriptionEn', 'string')) {
                $dish->setDescriptionEn($parameters['descriptionEn']);
            }
            if ($this->isParamValid($parameters, 'category', 'integer')) {
                $dish->setCategory($this->categoryRepository->find($parameters['category']));
            }

            $this->em->persist($dish);
            $this->em->flush();

            return new JsonResponse(['status' => 'success'], 200);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['status' => $e->getMessage()], 500);
        }
    }

    public function delete(Dish $dish): JsonResponse
    {
        try {
            // hide the dish if it has been assigned to a meal, else delete it
            if ($this->dishRepository->hasDishAssociatedMeals($dish)) {
                $dish->setEnabled(false);
                $this->em->persist($dish);
            } else {
                $this->em->remove($dish);
            }
            $this->em->flush();

            return new JsonResponse(['status' => 'success'], 200);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['status' => $e->getMessage()], 500);
        }
    }

    public function update(Dish $dish, Request $request): JsonResponse
    {
        try {
            $parameters = json_decode($request->getContent(), true);
            if ($this->isParamValid($parameters, 'titleDe', 'string')) {
                $dish->setTitleDe($parameters['titleDe']);
            }
            if ($this->isParamValid($parameters, 'titleEn', 'string')) {
                $dish->setTitleEn($parameters['titleEn']);
            }
            if ($this->isParamValid($parameters, 'oneServingSize', 'boolean')) {
                $dish->setOneServingSize($parameters['oneServingSize']);
                if ($dish->hasVariations()) {
                    /** @var Dish $variation */
                    foreach ($dish->getVariations() as $variation) {
                        $variation->setOneServingSize($parameters['oneServingSize']);
                    }
                }
            }
            if ($this->isParamValid($parameters, 'descriptionDe', 'string')) {
                $dish->setDescriptionDe($parameters['descriptionDe']);
            }
            if ($this->isParamValid($parameters, 'descriptionEn', 'string')) {
                $dish->setDescriptionEn($parameters['descriptionEn']);
            }
            if ($this->isParamValid($parameters, 'category', 'integer')) {
                $dish->setCategory($this->categoryRepository->find($parameters['category']));
            }

            $this->em->persist($dish);
            $this->em->flush();

            return new JsonResponse($dish, 200);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['status' => $e->getMessage()], 500);
        }
    }

    /**
     * Checks wether the parameter at a given key exists and is valid.
     *
     * @param array  $parameters decoded json content
     * @param string $key        key for the parameter to be checked
     * @param string $type       the expected type of the parameter
     */
    protected function isParamValid($parameters, $key, $type): bool
    {
        return isset($parameters[$key])
            && null !== $parameters[$key]
            && $type === gettype($parameters[$key]);
    }
}
