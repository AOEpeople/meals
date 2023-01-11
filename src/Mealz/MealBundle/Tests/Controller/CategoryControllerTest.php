<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Controller;

use App\Mealz\MealBundle\Entity\Category;
use App\Mealz\MealBundle\Repository\CategoryRepository;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use Symfony\Component\DomCrawler\Crawler;

/**
 * Class CategoryAbstractControllerTest.
 */
class CategoryControllerTest extends AbstractControllerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadRoles(),
            new LoadUsers(self::$container->get('security.user_password_encoder.generic')),
        ]);

        $this->loginAs(self::USER_KITCHEN_STAFF);
    }

    public function testNewAction(): void
    {
        $this->markTestSkipped('not implemented');
        // Create form data
        $form['category'] = [
            'title_de' => 'category-form-title-de',
            'title_en' => 'category-form-title-en',
            '_token' => $this->getFormCSRFToken('/category/form', 'form #category__token'),
        ];

        // Call controller action
        $this->client->request('POST', '/category/new', $form);

        // Get persisted entity
        $categoryRepository = self::$container->get(CategoryRepository::class);
        $category = $categoryRepository->findOneBy([
            'title_de' => 'category-form-title-de',
            'title_en' => 'category-form-title-en',
        ]);

        $this->assertInstanceOf(Category::class, $category);
    }

    public function testListAction(): void
    {
        $this->markTestSkipped('not implemented');
        $category = $this->createCategory();
        $this->persistAndFlushAll([$category]);

        // Request
        $crawler = $this->client->request('GET', '/category');

        // Get data for assertions from response
        $heading = $crawler->filter('h1')->first()->text();
        $categoryTitle = $crawler->filter('.table-row .category-title')->first()->text();

        // Assertions
        $this->assertEquals('List of categories', trim($heading));
        $this->assertEquals($category->getTitle(), trim($categoryTitle));
    }

    public function testGetPreFilledFormAction(): void
    {
        $this->markTestSkipped('not implemented');
        // Create test data
        $category = $this->createCategory();
        $categoryAsArray = [
            'title_de' => $category->getTitleDe(),
            'title_en' => $category->getTitleEn(),
        ];
        $this->persistAndFlushAll([$category]);

        // Request
        $this->client->request('GET', '/category/form/' . $category->getSlug());
        $crawler = $this->getRawResponseCrawler();

        // Check if form is loaded
        $node = $crawler->filterXPath('//form[@action="/category/' . $category->getSlug() . '/edit"]');
        $this->assertSame($node->count(), 1);

        // Copy form values in array for comparison
        $form = $crawler->selectButton('Save')->form();
        $formCategoryAsArray = [
            'title_de' => $form->get('category[title_de]')->getValue(),
            'title_en' => $form->get('category[title_en]')->getValue(),
        ];

        // Assertions
        $this->assertEquals($categoryAsArray, $formCategoryAsArray);
    }

    public function testEditAction(): void
    {
        $this->markTestSkipped('not implemented');
        $category = $this->createCategory();
        $this->persistAndFlushAll([$category]);

        $form['category'] = [
            'title_de' => 'category-form-edited-title-de',
            'title_en' => 'category-form-edited-title-en',
            '_token' => $this->getFormCSRFToken('/category/form/' . $category->getSlug(), 'form #category__token'),
        ];

        $this->client->request('POST', '/category/' . $category->getSlug() . '/edit', $form);
        $categoryRepository = self::$container->get(CategoryRepository::class);
        unset($form['category']['category']);
        unset($form['category']['_token']);
        $editedCategory = $categoryRepository->findOneBy($form['category']);

        $this->assertInstanceOf(Category::class, $editedCategory);
        $this->assertEquals($category->getId(), $editedCategory->getId());
    }

    public function testEditActionOfNonExistingCategory(): void
    {
        $this->markTestSkipped('not implemented');
        $this->client->request('POST', '/category/non-existing-category/edit');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteAction(): void
    {
        $this->markTestSkipped('not implemented');
        $category = $this->createCategory();
        $this->persistAndFlushAll([$category]);

        $categoryId = $category->getId();
        $this->client->request('GET', '/category/' . $category->getSlug() . '/delete');
        $categoryRepository = self::$container->get(CategoryRepository::class);
        $queryResult = $categoryRepository->find($categoryId);

        $this->assertNull($queryResult);
    }

    public function testDeleteOfNonExistingCategory(): void
    {
        $this->markTestSkipped('not implemented');
        $this->client->request('GET', '/category/non-existing-category/delete');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
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
