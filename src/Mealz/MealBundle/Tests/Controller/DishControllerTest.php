<?php

namespace Mealz\MealBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Symfony\Component\DomCrawler\Crawler;

class DishAbstractControllerTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->createAdminClient();
        $this->mockServices();
        $this->clearAllTables();
    }

    public function testGetEmptyFormAction()
    {
        $this->client->request('GET', '/dish/form');
        $crawler = $this->getRawResponseCrawler();
        $node = $crawler->filterXPath('//form[@action="/dish/new"]');
        $this->assertTrue($node->count() === 1);
    }

    public function testNewAction()
    {
        // Create form data
        $token = $this->client->getContainer()->get('form.csrf_provider')->generateCsrfToken('dish_type');
        $form['dish'] = array(
            'title_de' => 'dish-form-title-de',
            'title_en' => 'dish-form-title-en',
            'description_de' => 'dish-form-desc-de',
            'description_en' => 'dish-form-desc-en',
            'category' => '',
            '_token' => $token
        );

        // Call controller action
        $this->client->request('POST', '/dish/new', $form);

        // Get persisted entity
        /** @var EntityManager $em */
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $dishRepository = $em->getRepository('MealzMealBundle:Dish');
        $dish = $dishRepository->findOneBy(array(
            'title_de' => 'dish-form-title-de',
            'title_en' => 'dish-form-title-en',
            'description_de' => 'dish-form-desc-de',
            'description_en' => 'dish-form-desc-en',
        ));

        // Assertions
        $this->assertInstanceOf('\Mealz\MealBundle\Entity\Dish', $dish);
    }

    public function testListAction()
    {
        $dish = $this->createDish();
        $this->persistAndFlushAll(array($dish));

        // Request
        $crawler = $this->client->request('GET', '/dish');

        // Get data for assertions from response
        $heading = $crawler->filter('h1')->first()->text();
        $dishTitle = $crawler->filter('.table-row .dish-title')->first()->text();

        // Assertions
        $this->assertEquals('List of dishes', trim($heading));
        $this->assertEquals($dish->getTitle(), trim($dishTitle));
    }

    public function testGetPreFilledFormAction()
    {
        // Create test data
        $dish = $this->createDish();
        $dishAsArray = array(
            'title_de' => $dish->getTitleDe(),
            'title_en' => $dish->getTitleEn()
        );
        $this->persistAndFlushAll(array($dish));

        // Request
        $this->client->request('GET', '/dish/form/' . $dish->getSlug());
        $crawler = $this->getRawResponseCrawler();

        // Check if form is loaded
        $node = $crawler->filterXPath('//form[@action="/dish/' . $dish->getSlug() . '/edit"]');
        $this->assertTrue($node->count() === 1);

        // Copy form values in array for comparison
        $form = $crawler->selectButton('Save')->form();
        $formDishAsArray = array(
            'title_de' => $form->get('dish[title_de]')->getValue(),
            'title_en' => $form->get('dish[title_en]')->getValue()
        );

        // Assertions
        $this->assertEquals($dishAsArray, $formDishAsArray);
    }

    public function testEditAction()
    {
        $dish = $this->createDish();
        $this->persistAndFlushAll(array($dish));

        $token = $this->client->getContainer()->get('form.csrf_provider')->generateCsrfToken('dish_type');
        $form['dish'] = array(
            'title_de' => 'dish-form-edited-title-de',
            'title_en' => 'dish-form-edited-title-en',
            'description_de' => 'dish-form-edited-desc-de',
            'description_en' => 'dish-form-edited-desc-en',
            'category' => '',
            '_token' => $token
        );

        $this->client->request('POST', '/dish/' . $dish->getSlug() . '/edit', $form);
        $dishRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Dish');
        unset($form['dish']['category']);
        unset($form['dish']['_token']);
        $editedDish = $dishRepository->findOneBy($form['dish']);

        $this->assertInstanceOf('\Mealz\MealBundle\Entity\Dish', $editedDish);
        $this->assertEquals($dish->getId(), $editedDish->getId());
    }

    public function testEditActionOfNonExistingDish()
    {
        $this->client->request('POST', '/dish/non-existing-dish/edit');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    public function testDeleteAction()
    {
        $dish = $this->createDish();
        $this->persistAndFlushAll(array($dish));

        $dishId = $dish->getId();
        $this->client->request('GET', '/dish/' . $dish->getSlug() . '/delete');
        $dishRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Dish');
        $queryResult = $dishRepository->find($dishId);

        $this->assertNull($queryResult);
    }

    public function testDeleteOfNonExistingDish()
    {
        $this->client->request('GET', '/dish/non-existing-dish/delete');
        $this->assertEquals(404,$this->client->getResponse()->getStatusCode());
    }

    protected function getRawResponseCrawler()
    {
        $content = $this->client->getResponse()->getContent();
        $uri = 'http://www.mealz.local';
        return new Crawler(json_decode($content), $uri);
    }
}