<?php

namespace App\Mealz\MealBundle\Tests\Controller;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DomCrawler\Crawler;

class DishControllerTest extends AbstractControllerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadWeeks(),
            new LoadDays(),
            new LoadCategories(),
            new LoadDishes(),
            new LoadDishVariations(),
            new LoadMeals(),
            new LoadRoles(),
            new LoadUsers(self::$container->get('security.user_password_encoder.generic')),
        ]);

        $this->loginAs(self::USER_KITCHEN_STAFF);
    }

    /**
     * Test calling a form for new dish.
     */
    public function testGetEmptyFormAction(): void
    {
        $this->client->request('GET', '/dish/form');
        $crawler = $this->getRawResponseCrawler();
        $node = $crawler->filterXPath('//form[@action="/dish/new"]');
        $this->assertSame($node->count(), 1);
    }

    /**
     * Test creating a new dish.
     */
    public function testNewAction(): void
    {
        // Create form data
        $form = [
            'dish' => [
                'title_de' => 'dish-form-title-de',
                'title_en' => 'dish-form-title-en',
                'description_de' => 'dish-form-desc-de',
                'description_en' => 'dish-form-desc-en',
                'category' => '',
                '_token' => $this->getFormCSRFToken('/dish/form', 'form #dish__token'),
            ],
        ];

        // Call controller action
        $this->client->request('POST', '/dish/new', $form);

        // Get persisted entity
        /** @var EntityManager $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $dishRepository = $entityManager->getRepository(Dish::class);
        $dish = $dishRepository->findOneBy([
            'title_de' => 'dish-form-title-de',
            'title_en' => 'dish-form-title-en',
            'description_de' => 'dish-form-desc-de',
            'description_en' => 'dish-form-desc-en',
        ]);

        $this->assertNotNull($dish);
        $this->assertInstanceOf(Dish::class, $dish);
    }

    /**
     * Test adding a new new dish and find it listed in dishes list.
     */
    public function testListAction(): void
    {
        $dish = $this->createDish();
        $this->persistAndFlushAll([$dish]);

        // Request
        $crawler = $this->client->request('GET', '/dish');

        // Get data for assertions from response
        $heading = $crawler->filter('h1')->first()->text();

        $dishTitles = $crawler->filter('.table-row .dish-title');
        $found = false;

        foreach ($dishTitles as $dishTitle) {
            if ($dish->getTitle() === trim($dishTitle->nodeValue)) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Dish not found');
        $this->assertEquals('List of dishes', trim($heading));
    }

    /**
     * Test if a dish is selected for editing the form must be prefilled
     * with the dishes data.
     */
    public function testGetPreFilledFormAction(): void
    {
        // Create test data
        $dish = $this->createDish();
        $dishAsArray = [
            'title_de' => $dish->getTitleDe(),
            'title_en' => $dish->getTitleEn(),
        ];
        $this->persistAndFlushAll([$dish]);

        // Request
        $this->client->request('GET', '/dish/form/' . $dish->getSlug());
        $crawler = $this->getRawResponseCrawler();

        // Check if form is loaded
        $node = $crawler->filterXPath('//form[@action="/dish/' . $dish->getSlug() . '/edit"]');
        $this->assertSame($node->count(), 1);

        // Copy form values in array for comparison
        $form = $crawler->selectButton('Save')->form();
        $formDishAsArray = [
            'title_de' => $form->get('dish[title_de]')->getValue(),
            'title_en' => $form->get('dish[title_en]')->getValue(),
        ];

        $this->assertEquals($dishAsArray, $formDishAsArray);
    }

    /**
     * Test a previously created dish can be edited in a form.
     */
    public function testEditAction(): void
    {
        $dish = $this->createDish();
        $this->persistAndFlushAll([$dish]);

        $form['dish'] = [
            'title_de' => 'dish-form-edited-title-de',
            'title_en' => 'dish-form-edited-title-en',
            'description_de' => 'dish-form-edited-desc-de',
            'description_en' => 'dish-form-edited-desc-en',
            'category' => '',
            '_token' => $this->getFormCSRFToken('/dish/form/' . $dish->getSlug(), 'form #dish__token'),
        ];

        $this->client->request('POST', '/dish/' . $dish->getSlug() . '/edit', $form);
        $dishRepository = $this->getDoctrine()->getRepository(Dish::class);
        unset($form['dish']['category'], $form['dish']['_token']);
        $editedDish = $dishRepository->findOneBy($form['dish']);

        $this->assertInstanceOf(Dish::class, $editedDish);
        $this->assertEquals($dish->getId(), $editedDish->getId());
    }

    /**
     * Test calling a non existing dish(ID) to be EDITED leads to a 404 error.
     */
    public function testEditActionOfNonExistingDish(): void
    {
        $this->client->request('POST', '/dish/non-existing-dish/edit');
        $this->assertSame(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test firing a dish DELETION from the admin backend deletes the dish from database.
     */
    public function testDeleteAction(): void
    {
        $dish = $this->createDish();
        $this->persistAndFlushAll([$dish]);

        $dishId = $dish->getId();
        $this->client->request('GET', '/dish/' . $dish->getSlug() . '/delete');
        $dishRepository = $this->getDoctrine()->getRepository(Dish::class);
        $queryResult = $dishRepository->find($dishId);

        $this->assertNull($queryResult);
    }

    /**
     * Test calling a non existing dish(ID) to be DELETED leads to a 404 error.
     */
    public function testDeleteOfNonExistingDish(): void
    {
        $this->client->request('GET', '/dish/non-existing-dish/delete');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test if a newly created dish is marked as new.
     */
    public function testIfNewDishIsNew(): void
    {
        // Create form data
        $form['dish'] = [
            'title_de' => 'dish-form-title-de',
            'title_en' => 'dish-form-title-en',
            'description_de' => 'dish-form-desc-de',
            'description_en' => 'dish-form-desc-en',
            'category' => '',
            '_token' => $this->getFormCSRFToken('/dish/form', 'form #dish__token'),
        ];

        // Call controller action
        $this->client->request('POST', '/dish/new', $form);

        // Get persisted entity
        /** @var EntityManager $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $dishRepository = $entityManager->getRepository(Dish::class);
        $dish = $dishRepository->findOneBy([
            'title_de' => 'dish-form-title-de',
            'title_en' => 'dish-form-title-en',
            'description_de' => 'dish-form-desc-de',
            'description_en' => 'dish-form-desc-en',
        ]);

        $this->assertNotNull($dish);
        $dishService = self::$container->get('mealz_meal.service.dish_service');
        $this->assertTrue($dishService->isNew($dish));
    }

    /**
     * Test if a often offered dish is not marked as new.
     */
    public function testIfOftenOfferedDishIsNotNew(): void
    {
        // Get persisted entity
        /** @var EntityManager $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $dishRepository = $entityManager->getRepository(Dish::class);
        $dish = $dishRepository->findOneBy([
            'slug' => 'braaaaaiiinnnzzzzzz',
        ]);

        $this->assertNotNull($dish);
        $dishService = self::$container->get('mealz_meal.service.dish_service');
        $this->assertFalse($dishService->isNew($dish));
    }

    protected function getRawResponseCrawler(): Crawler
    {
        $content = $this->client->getResponse()->getContent();
        $uri = 'http://www.mealz.local';

        return new Crawler($content, $uri);
    }
}
