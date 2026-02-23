<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Controller;

use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Repository\MealRepositoryInterface;
use Override;
use Symfony\Component\HttpFoundation\Response;

final class ParticipantControllerTest extends AbstractControllerTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        $this->clearAllTables();
        // load minimal fixtures required for meals and users
        $this->loadFixtures([
            new \App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories(),
            new \App\Mealz\MealBundle\DataFixtures\ORM\LoadWeeks(),
            new \App\Mealz\MealBundle\DataFixtures\ORM\LoadDays(),
            new \App\Mealz\MealBundle\DataFixtures\ORM\LoadDishes(),
            new \App\Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations(),
            new \App\Mealz\MealBundle\DataFixtures\ORM\LoadMeals(),
            new \App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles(),
            new \App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers(self::getContainer()->get('security.user_password_hasher')),
        ]);
    }

    public function testJoinAndLeaveMeal(): void
    {
        $this->loginAs(self::USER_STANDARD);

        /** @var MealRepositoryInterface $mealRepo */
        $mealRepo = self::getContainer()->get(MealRepositoryInterface::class);
        $meal = $mealRepo->getFutureMeals()[0];
        $this->assertNotNull($meal);

        // join meal
        $payload = json_encode([
            'mealID' => $meal->getId(),
            'dishSlugs' => [],
        ]);
        $this->client->request('POST', '/api/meal/participation', [], [], [], $payload);
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('participantId', $data);

        // leave meal
        $payload = json_encode(['mealId' => $meal->getId()]);
        $this->client->request('DELETE', '/api/meal/participation', [], [], [], $payload);
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
    }

    public function testOfferAndCancelMeal(): void
    {
        $this->loginAs(self::USER_STANDARD);

        /** @var MealRepositoryInterface $mealRepo */
        $mealRepo = self::getContainer()->get(MealRepositoryInterface::class);
        $meal = $mealRepo->getFutureMeals()[0];
        $this->assertNotNull($meal);

        // first join so we have a participation
        $payload = json_encode(['mealID' => $meal->getId(), 'dishSlugs' => []]);
        $this->client->request('POST', '/api/meal/participation', [], [], [], $payload);
        $data = json_decode($this->client->getResponse()->getContent(), true);
        $participantId = $data['participantId'];

        // offer meal
        $payload = json_encode(['mealId' => $meal->getId()]);
        $this->client->request('POST', '/api/meal/offer', [], [], [], $payload);
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        // cancel offer
        $this->client->request('DELETE', '/api/meal/offer', [], [], [], $payload);
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());
    }
}
