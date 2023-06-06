<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Controller;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\Entity\Category;
use App\Mealz\MealBundle\Repository\CategoryRepositoryInterface;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class CategoryAbstractControllerTest.
 */
class CategoryControllerTest extends AbstractControllerTestCase
{
    private EntityManagerInterface $em;
    private CategoryRepositoryInterface $categoryRepo;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadRoles(),
            new LoadUsers(self::$container->get('security.user_password_encoder.generic')),
            new LoadCategories(),
        ]);

        $this->categoryRepo = static::$container->get(CategoryRepositoryInterface::class);
        $this->em = static::$container->get(EntityManagerInterface::class);

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
            ], [
                'id' => $response[1]['id'],
                'titleDe' => 'Vegetarisch',
                'titleEn' => 'Vegetarian',
                'slug' => 'vegetarian',
            ], [
                'id' => $response[2]['id'],
                'titleDe' => 'Fleisch',
                'titleEn' => 'Meat',
                'slug' => 'meat',
            ],
        ];

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals($expectedCategories, $response);
    }

    public function testCreateCategory(): void
    {
        $parameters = json_encode(['titleDe' => 'testDe987', 'titleEn' => 'testEn987']);
        $this->client->request('POST', '/api/categories', [], [], [], $parameters);

        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['status' => 'success'], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testCreateCategoryFail(): void
    {
        $parameters = json_encode(['titleEn' => 'testEn987']);
        $this->client->request('POST', '/api/categories', [], [], [], $parameters);

        $this->assertEquals(500, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(['status' => 'Category titles not set or they already exist'], json_decode($this->client->getResponse()->getContent(), true));
    }

    public function testDeleteCategory(): void
    {
        $category = $this->categoryRepo->findOneBy(['slug' => 'others']);
        $this->assertInstanceOf(Category::class, $category);

        $this->client->request('DELETE', '/api/categories/others');
        $category = $this->categoryRepo->findOneBy(['slug' => 'others']);

        $this->assertNull($category);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
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
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());
        $this->assertEquals(json_encode($category), $this->client->getResponse()->getContent());
    }

    protected function getRawResponseCrawler(): Crawler
    {
        $content = $this->client->getResponse()->getContent();
        $uri = 'http://meals.test';

        return new Crawler($content, $uri);
    }

    protected function getJsonResponseCrawler(): Crawler
    {
        $content = $this->client->getResponse()->getContent();
        $uri = 'http://meals.test';

        return new Crawler(json_decode($content, true), $uri);
    }
}
