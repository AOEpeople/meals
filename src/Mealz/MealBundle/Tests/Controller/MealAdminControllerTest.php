<?php

namespace App\Mealz\MealBundle\Tests\Controller;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use DateTime;

class MealAdminControllerTest extends AbstractControllerTestCase
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

    public function testGetWeeks(): void
    {
        $meal = $this->getRecentMeal();

        // Request
        $this->client->request('GET', '/api/weeks');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // Get data for assertions from response
        $responseData = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertNotNull($responseData);

        // Assert that the response contains the expected data
        $found = false;
        foreach ($responseData as $week) {
            foreach ($week['days'] as $dayId => $day) {
                if ($dayId === $meal->getDay()->getId()) {
                    foreach ($day['meals'] as $parent) {
                        foreach ($parent as $child) {
                            if ($child['id'] === $meal->getId()) {
                                $found = true;
                                break;
                            }
                        }
                    }
                }
            }
        }

        $this->assertTrue($found, 'Expected meal not found in response');
    }

    public function testNew(): void
    {
        $date = new DateTime('+2 month');
        $year = (int) $date->format('Y');
        $week = (int) $date->format('W');
        $routeStr = '/api/weeks/' . $year . 'W' . $week;

        // Request
        $this->client->request('POST', $routeStr);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        // Get data for assertions with new request response
        $weekRepository = $this->getDoctrine()->getRepository(Week::class);
        $createdWeek = $weekRepository->findOneBy([
            'year' => $date->format('o'),
            'calendarWeek' => $date->format('W'),
        ]);

        $this->assertNotNull($createdWeek, 'Expected week not found in database');
        $this->assertEquals($createdWeek->getYear(), $year);
        $this->assertEquals($createdWeek->getCalendarWeek(), $week);

        // Trying to create the same week twice should fail
        $this->client->request('POST', $routeStr);
        $this->assertEquals(400, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('week already exists', $response['message']);
    }

    public function testCount(): void
    {
        $this->client->request('GET', '/api/meals/count');
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertNotNull($response);

        foreach ($response as $id => $count) {
            $this->assertIsInt($id);
            $this->assertIsInt($count);
        }
    }

    public function testEdit(): void
    {
        $date = new DateTime('+2 week');

        // Get data for assertions with new request response
        $weekRepository = $this->getDoctrine()->getRepository(Week::class);
        $createdWeek = $weekRepository->findOneBy([
            'year' => $date->format('o'),
            'calendarWeek' => $date->format('W'),
        ]);

        $this->assertNotNull($createdWeek);
        $this->assertInstanceOf(Week::class, $createdWeek);

        $foundDay = $createdWeek->getDays()[0];
        $foundMeal = $foundDay->getMeals()[0];
        $dishRepository = $this->getDoctrine()->getRepository(Dish::class);
        $testDish = $dishRepository->findOneBy(['parent' => null]);

        $this->assertNull($foundMeal);
        $this->assertNotNull($testDish);

        $testPutStr = '{
            "id": ' . $createdWeek->getId() . ',
            "days": [
                {
                    "meals": {
                        "0": [
                            {
                                "dishSlug": "' . $testDish->getSlug() . '",
                                "mealId": null,
                                "participationLimit": 0
                            }
                        ],
                        "-1": []
                    },
                    "id": ' . $createdWeek->getDays()[0]->getId() . ',
                    "enabled": true,
                    "date": {
                        "date": "' . $date->format('Y-m-d') . ' 12:00:00.000000",
                        "timezone_type": 3,
                        "timezone": "Europe\/Berlin"
                    },
                    "lockDate": null
                }
            ],
            "notify": false,
            "enabled": true
        }';

        $this->client->request('PUT', '/api/menu/' . $createdWeek->getId(), [], [], [], $testPutStr);
        $this->assertEquals(200, $this->client->getResponse()->getStatusCode());

        $createdWeek = $weekRepository->findOneBy([
            'year' => $date->format('o'),
            'calendarWeek' => $date->format('W'),
        ]);

        $foundDay = $createdWeek->getDays()[0];
        $foundMeal = $foundDay->getMeals()[0];

        $this->assertEquals($testDish->getId(), $foundMeal->getDish()->getId());
    }
}
