<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Category;
use App\Mealz\MealBundle\Service\CategoryService;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("is_granted('ROLE_KITCHEN_STAFF')")
 */
class CategoryController extends BaseListController
{
    private CategoryService $categorySrv;

    public function __construct(
        CategoryService $categorySrv
    ) {
        $this->categorySrv = $categorySrv;
    }

    /**
     * Send Categories Data.
     */
    public function getCategoriesData(): JsonResponse
    {
        $categories = $this->categorySrv->getAllCategories();

        return new JsonResponse($categories, 200);
    }

    public function editCategory(Request $request, Category $category): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);

        try {
            $category = $this->categorySrv->editCategory($parameters, $category);

            return new JsonResponse($category, 200);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['status' => $e->getMessage()], 500);
        }
    }

    public function deleteCategory(Category $category): JsonResponse
    {
        try {
            $this->categorySrv->deleteCategory($category);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(null, 500);
        }

        return new JsonResponse(['status' => 'success'], 200);
    }

    public function createCategory(Request $request): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);

        try {
            $this->categorySrv->createCategory($parameters);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(null, 500);
        }

        return new JsonResponse(['status' => 'success'], 200);
    }
}
