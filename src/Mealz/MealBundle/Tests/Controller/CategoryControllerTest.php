<?php

namespace Mealz\MealBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DomCrawler\Crawler;

use Mealz\UserBundle\DataFixtures\ORM\LoadUsers;

/**
 * Class CategoryAbstractControllerTest
 * @package Mealz\MealBundle\Tests\Controller
 */
class CategoryControllerTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->createAdminClient();
        $this->clearAllTables();
        $this->loadFixtures(
            [
                new LoadUsers($this->client->getContainer()),
            ]
        );
    }

    public function testNewAction()
    {
        // Create form data
        $form['category'] = array(
            'title_de' => 'category-form-title-de',
            'title_en' => 'category-form-title-en',
        );

        // Call controller action
        $this->client->request('POST', '/category/new', $form);

        // Get persisted entity
        /** @var EntityManager $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $categoryRepository = $entityManager->getRepository('MealzMealBundle:Category');
        $category = $categoryRepository->findOneBy(
            array(
                'title_de' => 'category-form-title-de',
                'title_en' => 'category-form-title-en',
            )
        );

        // Assertions
        $this->assertInstanceOf('\Mealz\MealBundle\Entity\Category', $category);
    }

    public function testListAction()
    {
        $category = $this->createCategory();
        $this->persistAndFlushAll(array($category));

        // Request
        $crawler = $this->client->request('GET', '/category');

        // Get data for assertions from response
        $heading = $crawler->filter('h1')->first()->text();
        $categoryTitle = $crawler->filter('.table-row .category-title')->first()->text();

        // Assertions
        $this->assertEquals('List of categories', trim($heading));
        $this->assertEquals($category->getTitle(), trim($categoryTitle));
    }

    public function testGetPreFilledFormAction()
    {
        // Create test data
        $category = $this->createCategory();
        $categoryAsArray = array(
            'title_de' => $category->getTitleDe(),
            'title_en' => $category->getTitleEn(),
        );
        $this->persistAndFlushAll(array($category));

        // Request
        $this->client->request('GET', '/category/form/'.$category->getSlug());
        $crawler = $this->getJsonResponseCrawler();

        // Check if form is loaded
        $node = $crawler->filterXPath('//form[@action="/category/'.$category->getSlug().'/edit"]');
        $this->assertTrue($node->count() === 1);

        // Copy form values in array for comparison
        $form = $crawler->selectButton('Save')->form();
        $formCategoryAsArray = array(
            'title_de' => $form->get('category[title_de]')->getValue(),
            'title_en' => $form->get('category[title_en]')->getValue(),
        );

        // Assertions
        $this->assertEquals($categoryAsArray, $formCategoryAsArray);
    }

    public function testEditAction()
    {
        $category = $this->createCategory();
        $this->persistAndFlushAll(array($category));

        $form['category'] = array(
            'title_de' => 'category-form-edited-title-de',
            'title_en' => 'category-form-edited-title-en',
        );

        $this->client->request('POST', '/category/'.$category->getSlug().'/edit', $form);
        $categoryRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Category');
        unset($form['category']['category']);
        unset($form['category']['_token']);
        $editedCategory = $categoryRepository->findOneBy($form['category']);

        $this->assertInstanceOf('\Mealz\MealBundle\Entity\Category', $editedCategory);
        $this->assertEquals($category->getId(), $editedCategory->getId());
    }

    public function testEditActionOfNonExistingCategory()
    {
        $this->client->request('POST', '/category/non-existing-category/edit');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteAction()
    {
        $category = $this->createCategory();
        $this->persistAndFlushAll(array($category));

        $categoryId = $category->getId();
        $this->client->request('GET', '/category/'.$category->getSlug().'/delete');
        $categoryRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Category');
        $queryResult = $categoryRepository->find($categoryId);

        $this->assertNull($queryResult);
    }

    public function testDeleteOfNonExistingCategory()
    {
        $this->client->request('GET', '/category/non-existing-category/delete');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    protected function getRawResponseCrawler()
    {
        $content = $this->client->getResponse()->getContent();
        $uri = 'http://meals.test';

        return new Crawler($content, $uri);
    }

    protected function getJsonResponseCrawler()
    {
        $content = $this->client->getResponse()->getContent();
        $uri = 'http://meals.test';

        return new Crawler(json_decode($content, true), $uri);
    }
}
