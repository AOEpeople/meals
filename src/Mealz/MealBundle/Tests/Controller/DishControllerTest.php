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
use Symfony\Component\HttpFoundation\Response;

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
            new LoadUsers(self::getContainer()->get('security.user_password_hasher')),
        ]);

        $this->loginAs(self::USER_KITCHEN_STAFF);
    }

    /**
     * Test creating a new dish.
     */
    public function testNew(): void
    {
        // Create data for request
        $data = json_encode([
            'titleDe' => 'Test De 123',
            'titleEn' => 'Test En 123',
            'oneServingSize' => false,
        ]);

        // Call controller action
        $this->client->request('POST', '/api/dishes', [], [], [], $data);

        // Get persisted entity
        /** @var EntityManager $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $dishRepository = $entityManager->getRepository(Dish::class);
        $dish = $dishRepository->findOneBy([
            'title_de' => 'Test De 123',
            'title_en' => 'Test En 123',
        ]);

        $this->assertNotNull($dish);
        $this->assertInstanceOf(Dish::class, $dish);
    }

    /**
     * Test adding a new dish and find it listed in dishes list.
     */
    public function testGetDishes(): void
    {
        $newDish = $this->createDish();
        $this->persistAndFlushAll([$newDish]);

        // Request
        $this->client->request('GET', '/api/dishes');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Get data for assertions from response
        $response = json_decode($this->client->getResponse()->getContent(), true);

        $found = false;

        foreach ($response as $dish) {
            if ($newDish->getTitleDe() === $dish['titleDe'] && $newDish->getTitleEn() === $dish['titleEn']) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Dish not found');
    }

    /**
     * Test a previously created dish can be updated.
     */
    public function testUpdate(): void
    {
        $dish = $this->createDish();
        $this->persistAndFlushAll([$dish]);

        $data = json_encode([
            'titleDe' => 'Test De 321',
            'titleEn' => 'Test En 321',
            'oneServingSize' => true,
            'descriptionDe' => 'Test De Description',
            'descriptionEn' => 'Test En Description',
        ]);

        $this->client->request('PUT', '/api/dishes/'.$dish->getSlug(), [], [], [], $data);
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $dishRepository = $this->getDoctrine()->getRepository(Dish::class);
        $editedDish = $dishRepository->findOneBy([
            'title_de' => 'Test De 321',
            'title_en' => 'Test En 321',
        ]);

        $this->assertInstanceOf(Dish::class, $editedDish);
        $this->assertEquals($dish->getId(), $editedDish->getId());
    }

    /**
     * Test calling a non existing dish(ID) to be EDITED leads to a 404 error.
     */
    public function testEditActionOfNonExistingDish(): void
    {
        $this->client->request('PUT', '/api/dishes/non-existing-dish');
        $this->assertSame(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode(), $this->client->getResponse()->getContent());
    }

    /**
     * Test firing a dish DELETION from the admin backend deletes the dish from database.
     */
    public function testDeleteAction(): void
    {
        $dish = $this->createDish();
        $this->persistAndFlushAll([$dish]);

        $dishId = $dish->getId();
        $this->client->request('DELETE', '/api/dishes/'.$dish->getSlug());
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $dishRepository = $this->getDoctrine()->getRepository(Dish::class);
        $queryResult = $dishRepository->find($dishId);

        $this->assertNull($queryResult);
    }

    /**
     * Test calling a non existing dish(ID) to be DELETED leads to a 404 error.
     */
    public function testDeleteOfNonExistingDish(): void
    {
        $this->client->request('DELETE', '/api/dishes/non-existing-dish');
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test if a newly created dish is marked as new.
     */
    public function testIfNewDishIsNew(): void
    {
        $this->markTestSkipped('not implemented');
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
        $dishService = self::getContainer()->get('mealz_meal.service.dish_service');
        $this->assertTrue($dishService->isNew($dish));
    }
}
