<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\Entity\Category;
use App\Mealz\MealBundle\Repository\CategoryRepositoryInterface;
use App\Mealz\MealBundle\Service\CategoryService;
use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use Doctrine\ORM\EntityManagerInterface;

class CategoryServiceTest extends AbstractDatabaseTestCase
{
    private EntityManagerInterface $em;
    private CategoryRepositoryInterface $categoryRepo;
    private CategoryService $categoryService;

    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->loadFixtures([new LoadCategories()]);

        /* @var CategoryService $categoryService */
        $this->categoryService = static::$container->get(CategoryService::class);
        $this->categoryRepo = static::$container->get(CategoryRepositoryInterface::class);
        $this->em = static::$container->get(EntityManagerInterface::class);
    }

    /**
     * @test
     *
     * @testdox Create a new Category
     */
    public function createNewCategory(): void
    {
        $parameters = ['title_de' => 'Test_de_1234', 'title_en' => 'Test_en_1234'];

        $category = $this->categoryRepo->findOneBy(['title_de' => 'Test_de_1234']);
        $this->assertNull($category);

        $this->categoryService->createCategory($parameters);
        $this->em->clear();

        $category = $this->categoryRepo->findOneBy(['title_de' => 'Test_de_1234']);
        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals('Test_de_1234', $category->getTitleDe());
        $this->assertEquals('Test_en_1234', $category->getTitleEn());
        $this->assertEquals('test-en-1234', $category->getSlug());
    }

    /**
     * @test
     *
     * @testdox Delete a Category
     */
    public function deleteCategory(): void
    {
        $categories = $this->categoryRepo->findAll();
        $this->assertInstanceOf(Category::class, $categories[0]);
        /** @var Category $category */
        $category = $categories[0];

        $categoryId = $category->getId();
        $this->categoryService->deleteCategory($categoryId);
        $this->em->clear();

        $category = $this->categoryRepo->find($categoryId);
        $this->assertNull($category);
    }

    /**
     * @test
     *
     * @testdox Edit a category
     */
    public function editCategory(): void
    {
        $categories = $this->categoryRepo->findAll();
        $this->assertInstanceOf(Category::class, $categories[0]);
        /** @var Category $category */
        $category = $categories[0];

        $categoryId = $category->getId();
        $this->categoryService->editCategory(['title_en' => 'Test_en_1234', 'title_de' => 'Test_de_1234'], $categoryId);

        $this->em->clear();
        $category = $this->categoryRepo->find($categoryId);

        $this->assertNotNull($category);
        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals('Test_en_1234', $category->getTitleEn());
        $this->assertEquals('Test_de_1234', $category->getTitleDe());
    }
}
