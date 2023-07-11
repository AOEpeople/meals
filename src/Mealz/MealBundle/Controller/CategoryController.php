<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Category;
use App\Mealz\MealBundle\Repository\CategoryRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("is_granted('ROLE_KITCHEN_STAFF')")
 */
class CategoryController extends BaseListController
{
    private CategoryRepositoryInterface $categoryRepo;
    private EntityManagerInterface $em;

    public function __construct(
        CategoryRepositoryInterface $categoryRepo,
        EntityManagerInterface $em
    ) {
        $this->categoryRepo = $categoryRepo;
        $this->em = $em;
    }

    /**
     * Send Categories Data.
     */
    public function getCategories(): JsonResponse
    {
        $categories = $this->categoryRepo->findAll();

        return new JsonResponse($categories, 200);
    }

    /**
     * Updates a category.
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);

        try {
            if (isset($parameters['titleEn'])) {
                $category->setTitleEn($parameters['titleEn']);
            }
            if (isset($parameters['titleDe'])) {
                $category->setTitleDe($parameters['titleDe']);
            }

            $this->em->persist($category);
            $this->em->flush();

            return new JsonResponse($category, 200);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Deletes a category.
     */
    public function delete(Category $category): JsonResponse
    {
        try {
            $this->em->remove($category);
            $this->em->flush();
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['message' => $e->getMessage()], 500);
        }

        return new JsonResponse(null, 200);
    }

    /**
     * Creates a new category.
     */
    public function new(Request $request): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);

        if (isset($parameters['titleDe']) && isset($parameters['titleEn']) && null === $this->getCategoryByTitleEn($parameters['titleEn']) && null === $this->getCategoryByTitleDe($parameters['titleDe'])) {
            $category = new Category();
            $category->setTitleEn($parameters['titleEn']);
            $category->setTitleDe($parameters['titleDe']);
            $this->em->persist($category);
            $this->em->flush();
        } else {
            return new JsonResponse(['message' => 'Category titles not set or they already exist'], 500);
        }

        return new JsonResponse(null, 200);
    }

    private function getCategoryByTitleEn(string $title): ?Category
    {
        return $this->categoryRepo->findOneBy(['title_en' => $title]);
    }

    private function getCategoryByTitleDe(string $title): ?Category
    {
        return $this->categoryRepo->findOneBy(['title_de' => $title]);
    }
}
