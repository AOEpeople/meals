<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Controller;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadEvents;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use DateTime;
use Override;
use Symfony\Component\HttpFoundation\Response;

final class WeekCreateControllerTest extends AbstractControllerTestCase
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

    public function testNew(): void
    {
        $date = new DateTime('+2 month');
        $year = $date->format('o');
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
}
