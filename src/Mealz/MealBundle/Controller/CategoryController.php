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

        return new JsonResponse($categories, \Symfony\Component\HttpFoundation\Response::HTTP_OK);
    }

    /**
     * Updates a category.
     */
    public function update(Request $request, Category $category): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);

        try {
            if (true === isset($parameters['titleEn'])) {
                $category->setTitleEn($parameters['titleEn']);
            }
            if (true === isset($parameters['titleDe'])) {
                $category->setTitleDe($parameters['titleDe']);
            }

            $this->em->persist($category);
            $this->em->flush();

            return new JsonResponse($category, \Symfony\Component\HttpFoundation\Response::HTTP_OK);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['message' => $e->getMessage()], \Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR);
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

            return new JsonResponse(['message' => $e->getMessage()], \Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(null, \Symfony\Component\HttpFoundation\Response::HTTP_OK);
    }

    /**
     * Creates a new category.
     */
    public function new(Request $request): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);

        if (
            true === isset($parameters['titleDe']) &&
            true === isset($parameters['titleEn']) &&
            null === $this->getCategoryByTitleEn($parameters['titleEn']) &&
            null === $this->getCategoryByTitleDe($parameters['titleDe'])
        ) {
            $category = new Category();
            $category->setTitleEn($parameters['titleEn']);
            $category->setTitleDe($parameters['titleDe']);
            $this->em->persist($category);
            $this->em->flush();
        } else {
            return new JsonResponse(['message' => '301: Category titles not set or they already exist'], \Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(null, \Symfony\Component\HttpFoundation\Response::HTTP_OK);
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
