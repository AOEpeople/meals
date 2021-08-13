<?php

namespace App\Mealz\MealBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DomCrawler\Crawler;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;

class DishAbstractControllerTest extends AbstractControllerTestCase
{
    protected function setUp(): void
    {
        $this->createAdminClient();
        //$this->mockServices();
        $this->clearAllTables();
        $this->loadFixtures(
            [
                new LoadWeeks(),
                new LoadDays(),
                new LoadCategories(),
                new LoadDishes(),
                new LoadDishVariations(),
                new LoadMeals(),
                new LoadUsers(self::$container->get('security.user_password_encoder.generic')),
            ]
        );
    }

    /**
     * Test calling a form for new dish
     */
    public function testGetEmptyFormAction()
    {
        $this->client->request('GET', '/dish/form');
        $crawler = $this->getRawResponseCrawler();
        $node = $crawler->filterXPath('//form[@action="/dish/new"]');
        $this->assertTrue($node->count() === 1);
    }

    /**
     * Test creating a new dish
     */
    public function testNewAction()
    {
        // Create form data
        $form['dish'] = array(
            'title_de' => 'dish-form-title-de',
            'title_en' => 'dish-form-title-en',
            'description_de' => 'dish-form-desc-de',
            'description_en' => 'dish-form-desc-en',
            'category' => '',
        );

        // Call controller action
        $this->client->request('POST', '/dish/new', $form);

        // Get persisted entity
        /** @var EntityManager $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $dishRepository = $entityManager->getRepository('MealzMealBundle:Dish');
        $dish = $dishRepository->findOneBy(
            array(
                'title_de' => 'dish-form-title-de',
                'title_en' => 'dish-form-title-en',
                'description_de' => 'dish-form-desc-de',
                'description_en' => 'dish-form-desc-en',
            )
        );

        // Assertions
        $this->assertNotNull($dish);
        $this->assertInstanceOf('\Mealz\MealBundle\Entity\Dish', $dish);
    }

    /**
     * Test adding a new new dish and find it listed in dishes list
     */
    public function testListAction()
    {
        $dish = $this->createDish();
        $this->persistAndFlushAll(array($dish));

        // Request
        $crawler = $this->client->request('GET', '/dish');

        // Get data for assertions from response
        $heading = $crawler->filter('h1')->first()->text();

        $dishTitles = $crawler->filter('.table-row .dish-title');
        $dishTitles->rewind();
        $found = false;

        if ($dishTitles->count() > 0) {
            while ($dishTitles->current() && $found == false) {
                $found = ($dish->getTitle() === trim($dishTitles->current()->nodeValue)) ? true : false;
                $dishTitles->next();
            }
        }

        // Assertions
        $this->assertTrue($found, 'Dish not found');
        $this->assertEquals('List of dishes', trim($heading));
    }

    /**
     * Test if a dish is selected for editing the form must be prefilled
     * with the dishes data
     */
    public function testGetPreFilledFormAction()
    {
        // Create test data
        $dish = $this->createDish();
        $dishAsArray = array(
            'title_de' => $dish->getTitleDe(),
            'title_en' => $dish->getTitleEn(),
        );
        $this->persistAndFlushAll(array($dish));

        // Request
        $this->client->request('GET', '/dish/form/'.$dish->getSlug());
        $crawler = $this->getRawResponseCrawler();

        // Check if form is loaded
        $node = $crawler->filterXPath('//form[@action="/dish/'.$dish->getSlug().'/edit"]');
        $this->assertTrue($node->count() === 1);

        // Copy form values in array for comparison
        $form = $crawler->selectButton('Save')->form();
        $formDishAsArray = array(
            'title_de' => $form->get('dish[title_de]')->getValue(),
            'title_en' => $form->get('dish[title_en]')->getValue(),
        );

        // Assertions
        $this->assertEquals($dishAsArray, $formDishAsArray);
    }

    /**
     * Test a previously created dish can be edited in a form
     */
    public function testEditAction()
    {
        $dish = $this->createDish();
        $this->persistAndFlushAll(array($dish));

        $form['dish'] = array(
            'title_de' => 'dish-form-edited-title-de',
            'title_en' => 'dish-form-edited-title-en',
            'description_de' => 'dish-form-edited-desc-de',
            'description_en' => 'dish-form-edited-desc-en',
            'category' => '',
        );

        $this->client->request('POST', '/dish/'.$dish->getSlug().'/edit', $form);
        $dishRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Dish');
        unset($form['dish']['category']);
        unset($form['dish']['_token']);
        $editedDish = $dishRepository->findOneBy($form['dish']);

        $this->assertInstanceOf('\Mealz\MealBundle\Entity\Dish', $editedDish);
        $this->assertEquals($dish->getId(), $editedDish->getId());
    }

    /**
     * Test calling a non existing dish(ID) to be EDITED leads to a 404 error
     */
    public function testEditActionOfNonExistingDish()
    {
        $this->client->request('POST', '/dish/non-existing-dish/edit');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test firing a dish DELETION from the admin backend deletes the dish from database
     */
    public function testDeleteAction()
    {
        $dish = $this->createDish();
        $this->persistAndFlushAll(array($dish));

        $dishId = $dish->getId();
        $this->client->request('GET', '/dish/'.$dish->getSlug().'/delete');
        $dishRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Dish');
        $queryResult = $dishRepository->find($dishId);

        $this->assertNull($queryResult);
    }

    /**
     * Test calling a non existing dish(ID) to be DELETED leads to a 404 error
     */
    public function testDeleteOfNonExistingDish()
    {
        $this->client->request('GET', '/dish/non-existing-dish/delete');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test if a newly created dish is marked as new
     * @test
     */
    public function testIfNewDishIsNew()
    {
        // Create form data
        $form['dish'] = array(
            'title_de' => 'dish-form-title-de',
            'title_en' => 'dish-form-title-en',
            'description_de' => 'dish-form-desc-de',
            'description_en' => 'dish-form-desc-en',
            'category' => '',
        );

        // Call controller action
        $this->client->request('POST', '/dish/new', $form);

        // Get persisted entity
        /** @var EntityManager $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $dishRepository = $entityManager->getRepository('MealzMealBundle:Dish');
        $dish = $dishRepository->findOneBy(
            array(
                'title_de' => 'dish-form-title-de',
                'title_en' => 'dish-form-title-en',
                'description_de' => 'dish-form-desc-de',
                'description_en' => 'dish-form-desc-en',
            )
        );

        // Assertions
        $this->assertNotNull($dish);
        $this->assertTrue($dish->isNew());
    }

    /**
     * Test if a often offered dish is not marked as new
     * @test
     */
    public function testIfOftenOffereDishIsNotNew()
    {
        // Get persisted entity
        /** @var EntityManager $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $dishRepository = $entityManager->getRepository('MealzMealBundle:Dish');
        $dish = $dishRepository->findOneBy(
            array(
                'slug' => 'braaaaaiiinnnzzzzzz'
            )
        );

        // Assertions
        $this->assertNotNull($dish);
        $this->assertFalse($dish->isNew());
    }

    protected function getRawResponseCrawler()
    {
        $content = $this->client->getResponse()->getContent();
        $uri = 'http://www.mealz.local';

        return new Crawler(json_decode($content), $uri);
    }
}
