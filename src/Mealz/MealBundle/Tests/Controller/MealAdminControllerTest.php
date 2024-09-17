<?php

namespace App\Mealz\MealBundle\Tests\Controller;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadEvents;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Event;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use DateTime;
use Override;
use Symfony\Component\HttpFoundation\Response;

final class MealAdminControllerTest extends AbstractControllerTestCase
{
    #[Override]
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
            new LoadEvents(),
            new LoadMeals(),
            new LoadRoles(),
            new LoadUsers(self::getContainer()->get('security.user_password_hasher')),
        ]);

        $this->loginAs(self::USER_KITCHEN_STAFF);
    }

    public function testGetWeeks(): void
    {
        $meal = $this->getRecentMeal(new DateTime('+3 day'));

        // Request
        $this->client->request('GET', '/api/weeks');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

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
        $year = $date->format('Y');
        $week = $date->format('W');
        $this->createFutureEmptyWeek($date);
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode(), 'Year: ' . $year . ', week: ' . $week . ', Status: ' . $this->client->getResponse()->getContent());

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
        $this->createFutureEmptyWeek($date);
        $this->assertEquals(Response::HTTP_INTERNAL_SERVER_ERROR, $this->client->getResponse()->getStatusCode());
        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertEquals('102: week already exists', $response['message']);
    }

    public function testCount(): void
    {
        $this->client->request('GET', '/api/meals/count');
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $response = json_decode($this->client->getResponse()->getContent(), true);
        $this->assertNotNull($response);

        foreach ($response as $id => $count) {
            $this->assertIsInt($id);
            $this->assertIsInt($count);
        }
    }

    public function testEdit(): void
    {
        $date = new DateTime('+2 month');
        $year = $date->format('o');
        $week = $date->format('W');

        // Create new week
        $this->createFutureEmptyWeek($date);
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // Get data for assertions with new request response
        $weekRepository = $this->getDoctrine()->getRepository(Week::class);
        $createdWeek = $weekRepository->findOneBy([
            'year' => $year,
            'calendarWeek' => $week,
        ]);

        $this->assertNotNull($createdWeek);
        $this->assertInstanceOf(Week::class, $createdWeek);

        $foundDay = $createdWeek->getDays()[1];
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
                    "event": null,
                    "enabled": true,
                    "date": {
                        "date": "' . $date->format('Y-m-d') . ' 12:00:00.000000",
                        "timezone_type": 3,
                        "timezone": "Europe\/Berlin"
                    },
                    "lockDate": null
                },
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
                    "id": ' . $createdWeek->getDays()[1]->getId() . ',
                    "event": null,
                    "enabled": true,
                    "date": {
                        "date": "' . $createdWeek->getDays()[1]->getDateTime()->format('Y-m-d') . ' 12:00:00.000000",
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
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $createdWeek = $weekRepository->findOneBy([
            'year' => $year,
            'calendarWeek' => $week,
        ]);

        $foundDay = $createdWeek->getDays()[1];
        $foundMeal = $foundDay->getMeals()[0];

        $this->assertEquals($testDish->getId(), $foundMeal->getDish()->getId());
    }

    private function createFutureEmptyWeek(DateTime $date): void
    {
        $year = $date->format('o');
        $week = $date->format('W');
        $dishRepository = $this->getDoctrine()->getRepository(Dish::class);
        $testDish = $dishRepository->findOneBy(['parent' => null]);
        $eventRepo = $this->getDoctrine()->getRepository(Event::class);
        $testEvent = $eventRepo->findOneBy(['deleted' => false]);

        $localDate = clone $date;
        $lockDate = clone $date;

        $routeStr = '/api/weeks/' . $year . 'W' . $week;
        $weekJson = '{
            "id": 49,
            "days": [
                {
                    "meals": {
                        "0": [],
                        "-1": []
                    },
                    "id": -1,
                    "event": null,
                    "enabled": true,
                    "date": {
                        "date": "' . $localDate->format('Y-m-d') . ' 12:00:00.000000",
                        "timezone_type": 3,"timezone": "Europe/Berlin"
                    },
                    "lockDate": {
                        "date": "' . $lockDate->modify('-1 day')->format('Y-m-d') . ' 16:00:00.000000",
                        "timezone_type": 3,
                        "timezone": "Europe/Berlin"
                    }
                },{
                    "meals": {
                        "0": [],
                        "-1": []
                    },
                    "id": -2,
                    "events": null,
                    "enabled": true,
                    "date": {
                        "date": "' . $localDate->modify('+1 day')->format('Y-m-d') . ' 12:00:00.000000",
                        "timezone_type": 3,
                        "timezone": "Europe/Berlin"
                    },
                    "lockDate": {
                        "date": "' . $lockDate->modify('+1 day')->format('Y-m-d') . ' 16:00:00.000000",
                        "timezone_type": 3,
                        "timezone": "Europe/Berlin"
                    }
                },{
                    "meals": {
                        "0": [],
                        "-1": []
                    },
                    "id": -3,
                    "events": null,
                    "enabled": true,
                    "date": {
                        "date": "' . $localDate->modify('+1 day')->format('Y-m-d') . ' 12:00:00.000000",
                        "timezone_type": 3,
                        "timezone": "Europe/Berlin"
                    },
                    "lockDate": {
                        "date": "' . $lockDate->modify('+1 day')->format('Y-m-d') . ' 16:00:00.000000",
                        "timezone_type": 3,
                        "timezone": "Europe/Berlin"
                    }
                },{
                    "meals": {
                        "0": [],
                        "-1": []
                    },
                    "id": -4,
                    "events": null,
                    "enabled": true,
                    "date": {
                        "date": "' . $localDate->modify('+1 day')->format('Y-m-d') . ' 12:00:00.000000",
                        "timezone_type": 3,
                        "timezone": "Europe/Berlin"
                    },
                    "lockDate": {
                        "date": "' . $lockDate->modify('+1 day')->format('Y-m-d') . ' 16:00:00.000000",
                        "timezone_type": 3,
                        "timezone": "Europe/Berlin"
                    }
                },{
                    "meals": {
                        "0": [],
                        "-1": []
                    },
                    "id": -5,
                    "events": ' . $testEvent->getId() . ',
                    "enabled": true,
                    "date": {
                        "date": "' . $localDate->modify('+1 day')->format('Y-m-d') . ' 12:00:00.000000",
                        "timezone_type": 3,
                        "timezone": "Europe/Berlin"
                    },
                    "lockDate": {
                        "date": "' . $lockDate->modify('+1 day')->format('Y-m-d') . ' 16:00:00.000000",
                        "timezone_type": 3,
                        "timezone": "Europe/Berlin"
                    }
                }
            ],
            "notify": false,
            "enabled": true
        }';

        // Request
        $this->client->request('POST', $routeStr, [], [], [], $weekJson);
    }
}
