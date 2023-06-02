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
        if (isset($parameters['titleDe']) && isset($parameters['titleEn']) && null === $this->getCategoryByTitleEn($parameters['titleEn']) && null === $this->getCategoryByTitleDe($parameters['titleDe'])) {
            $category = new Category();
            $category->setTitleEn($parameters['titleEn']);
            $category->setTitleDe($parameters['titleDe']);
            $this->em->persist($category);
            $this->em->flush();
        } else {
            throw new Exception('Category titles not set or they already exist');
        }
    }

    /**
     * Deletes a category.
     */
    public function deleteCategory(Category $category): void
    {
        $this->em->remove($category);
        $this->em->flush();
    }

    /**
     * Edit a category.
     */
    public function editCategory(array $parameters, Category $category): Category
    {
        if (isset($parameters['titleEn'])) {
            $category->setTitleEn($parameters['titleEn']);
        }
        if (isset($parameters['titleDe'])) {
            $category->setTitleDe($parameters['titleDe']);
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
}
