<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\Category;
use App\Mealz\MealBundle\Repository\CategoryRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

class CategoryService
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
     * Creates a new category.
     */
    public function createCategory(array $parameters): void
    {
        if (isset($parameters['title_de']) && isset($parameters['title_en']) && null === $this->getCategoryByTitleEn($parameters['title_en']) && null === $this->getCategoryByTitleDe($parameters['title_de'])) {
            $category = new Category();
            $category->setTitleEn($parameters['title_en']);
            $category->setTitleDe($parameters['title_de']);
            $this->em->persist($category);
            $this->em->flush();
        } else {
            throw new Exception('Category titles not set or they already exist');
        }
    }

    /**
     * Deletes a category.
     */
    public function deleteCategory(int $id): void
    {
        $category = $this->getCategoryById($id);
        if (null === $category) {
            throw new Exception('No category with this ID');
        } elseif (null === $category->getId()) {
            throw new Exception('Category ID not set');
        } elseif ($category->getId() !== $id) {
            throw new Exception('Category ID not equal to requested ID');
        }

        $this->em->remove($category);
        $this->em->flush();
    }

    /**
     * Edit a category.
     */
    public function editCategory(array $parameters, int $id): Category
    {
        /** @var Category $category */
        $category = $this->getCategoryById($id);

        if (null === $category) {
            throw new Exception('Category ID unknown');
        }

        if (isset($parameters['title_en'])) {
            $category->setTitleEn($parameters['title_en']);
        }
        if (isset($parameters['title_de'])) {
            $category->setTitleDe($parameters['title_de']);
        }

        $this->em->persist($category);
        $this->em->flush();

        return $category;
    }

    public function getAllCategories(): array
    {
        return $this->categoryRepo->findAll();
    }

    private function getCategoryByTitleEn(string $title): ?Category
    {
        return $this->categoryRepo->findOneBy(['title_en' => $title]);
    }

    private function getCategoryByTitleDe(string $title): ?Category
    {
        return $this->categoryRepo->findOneBy(['title_de' => $title]);
    }

    private function getCategoryById(int $id): ?Category
    {
        return $this->categoryRepo->find($id);
    }
}
