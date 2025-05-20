<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Controller;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\Entity\Category;
use App\Mealz\MealBundle\Repository\CategoryRepositoryInterface;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use Doctrine\ORM\EntityManagerInterface;
use Override;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CategoryAbstractControllerTest.
 */
final class CategoryControllerTest extends AbstractControllerTestCase
{
    private EntityManagerInterface $em;
    private CategoryRepositoryInterface $categoryRepo;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadRoles(),
            new LoadUsers(self::getContainer()->get('security.user_password_hasher')),
            new LoadCategories(),
        ]);

        $this->categoryRepo = static::getContainer()->get(CategoryRepositoryInterface::class);
        $this->em = static::getContainer()->get(EntityManagerInterface::class);

        $this->loginAs(self::USER_KITCHEN_STAFF);
    }

    public function testGetCategories(): void
    {
        $this->client->request('GET', '/api/categories');
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $expectedCategories = [
            [
                'id' => $response[0]['id'],
                'titleDe' => 'Sonstiges',
                'titleEn' => 'Others',
                'slug' => 'others',
            ],
            [
                'id' => $response[1]['id'],
                'titleDe' => 'Suppe',
                'titleEn' => 'Soup',
                'slug' => 'soup',
            ],
            [
                'id' => $response[2]['id'],
                'titleDe' => 'Pasta',
                'titleEn' => 'Pasta',
                'slug' => 'pasta',
            ],
            [
                'id' => $response[3]['id'],
                'titleDe' => 'Dessert',
                'titleEn' => 'Dessert',
                'slug' => 'dessert',
            ],
        ];

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals($expectedCategories, $response);
    }

    public function testCreateCategory(): void
    {
        $parameters = json_encode(['titleDe' => 'testDe987', 'titleEn' => 'testEn987']);
        $this->client->request('POST', '/api/categories', [], [], [], $parameters);

        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testCreateCategoryFail(): void
    {
        $parameters = json_encode(['titleEn' => 'testEn987']);
        $this->client->request('POST', '/api/categories', [], [], [], $parameters);

        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['message' => '301: Category titles not set or they already exist'], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testDeleteCategory(): void
    {
        $category = $this->categoryRepo->findOneBy(['slug' => 'others']);
        $this->assertInstanceOf(Category::class, $category);

        $this->client->request('DELETE', '/api/categories/others');
        $category = $this->categoryRepo->findOneBy(['slug' => 'others']);

        $this->assertNull($category);
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }

    public function testEditCategory(): void
    {
        $category = $this->categoryRepo->findOneBy(['slug' => 'others']);
        $this->assertInstanceOf(Category::class, $category);
        $categoryId = $category->getId();

        $parameters = json_encode(['titleDe' => 'testDe743', 'titleEn' => 'testEn743']);
        $this->client->request('PUT', '/api/categories/others', [], [], [], $parameters);

        $category = $this->categoryRepo->find($categoryId);
        $this->assertInstanceOf(Category::class, $category);
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(json_encode($category), $this->client->getResponse()->getContent());
    }
}
