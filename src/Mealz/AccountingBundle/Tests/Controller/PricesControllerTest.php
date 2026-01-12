<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Tests\Controller;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use App\Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use DateTimeImmutable;
use Override;
use Symfony\Component\HttpFoundation\Response;

final class PricesControllerTest extends AbstractControllerTestCase
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
            new LoadMeals(),
            new LoadRoles(),
            new LoadUsers(self::getContainer()->get('security.user_password_hasher')),
        ]);

        $this->loginAs(self::USER_KITCHEN_STAFF);
    }

    public function testListPricesIsValid(): void
    {
        $dateTime = new DateTimeImmutable('now');
        $dateTimeYearAsString = $dateTime->format('Y');
        $this->client->request('GET', '/api/prices');
        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(sprintf('{"prices":{"%s":{"year":%d,"price":4.4,"price_combined":6.4}}}', $dateTimeYearAsString, (int) $dateTimeYearAsString), $response->getContent());
    }

    public function testAddPriceIsValid(): void
    {
        $dateTime = new DateTimeImmutable('now')->modify('+1 year');
        $dateTimeYearAsString = $dateTime->format('Y');
        $this->client->request(
            'POST',
            '/api/price',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'year' => $dateTimeYearAsString,
                'price' => 4.4,
                'price_combined' => 6.4,
            ], JSON_THROW_ON_ERROR)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_CREATED, $response->getStatusCode());
        $this->assertSame(sprintf('{"message":"Price created successfully.","price":{"year":%s,"price":4.4,"price_combined":6.4}}', $dateTimeYearAsString), $response->getContent());
    }

    public function testAddPriceWithMissingRequiredFields(): void
    {
        $this->client->request(
            'POST',
            '/api/price',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'price' => 4.4,
                'price_combined' => 6.4,
            ], JSON_THROW_ON_ERROR)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('{"error":"1011: Missing required field: year."}', $response->getContent());
    }

    public function testAddPriceWithPriceCanNotBeLowerThanPreviousYear(): void
    {
        $dateTime = new DateTimeImmutable('now')->modify('+1 year');
        $dateTimeYearAsString = $dateTime->format('Y');
        $this->client->request(
            'POST',
            '/api/price',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'year' => $dateTimeYearAsString,
                'price' => 2,
                'price_combined' => 6.4,
            ], JSON_THROW_ON_ERROR)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('{"error":"1004: Price cannot be lower than previous year."}', $response->getContent());
    }

    public function testAddPriceWithCombinedPriceCanNotBeLowerThanPreviousYear(): void
    {
        $dateTime = new DateTimeImmutable('now')->modify('+1 year');
        $dateTimeYearAsString = $dateTime->format('Y');
        $this->client->request(
            'POST',
            '/api/price',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'year' => $dateTimeYearAsString,
                'price' => 4.4,
                'price_combined' => 2,
            ], JSON_THROW_ON_ERROR)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('{"error":"1005: Combined price cannot be lower than previous year."}', $response->getContent());
    }

    public function testAddPriceWithPriceCanNotBeHigherThanNextYear(): void
    {
        $dateTime = new DateTimeImmutable('now')->modify('-1 year');
        $dateTimeYearAsString = $dateTime->format('Y');
        $this->client->request(
            'PUT',
            '/api/price/' . $dateTimeYearAsString,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'price' => 10,
                'price_combined' => 6.4,
            ], JSON_THROW_ON_ERROR)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('{"error":"1006: Price cannot be higher than next year."}', $response->getContent());
    }

    public function testAddPriceWithCombinedPriceCanNotBeHigherThanNextYear(): void
    {
        $dateTime = new DateTimeImmutable('now')->modify('-1 year');
        $dateTimeYearAsString = $dateTime->format('Y');
        $this->client->request(
            'PUT',
            '/api/price/' . $dateTimeYearAsString,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'price' => 4.4,
                'price_combined' => 10,
            ], JSON_THROW_ON_ERROR)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_BAD_REQUEST, $response->getStatusCode());
        $this->assertSame('{"error":"1007: Combined price cannot be higher than next year."}', $response->getContent());
    }

    public function testAddPriceWithPriceAlreadyExistsForYear(): void
    {
        $dateTime = new DateTimeImmutable('now');
        $dateTimeYearAsString = $dateTime->format('Y');
        $this->client->request(
            'POST',
            '/api/price',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'year' => $dateTimeYearAsString,
                'price' => 4.4,
                'price_combined' => 6.4,
            ], JSON_THROW_ON_ERROR)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_CONFLICT, $response->getStatusCode());
        $this->assertSame('{"error":"1003: Prices for this year already exists."}', $response->getContent());
    }

    public function testDeletePriceIsValid(): void
    {
        $dateTime = new DateTimeImmutable('now');
        $dateTimeYearAsString = $dateTime->format('Y');
        $this->client->request('DELETE', '/api/price/' . $dateTimeYearAsString);

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame('{"message":"Price deleted."}', $response->getContent());
    }

    public function testDeletePriceWithPriceNotFound(): void
    {
        $dateTime = new DateTimeImmutable('now')->modify('+1 year');
        $dateTimeYearAsString = $dateTime->format('Y');
        $this->client->request('DELETE', '/api/price/' . $dateTimeYearAsString);

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame('{"error":"1012: Price not found."}', $response->getContent());
    }

    public function testEditPriceIsValid(): void
    {
        $dateTime = new DateTimeImmutable('now');
        $dateTimeYearAsString = $dateTime->format('Y');
        $this->client->request('PUT', '/api/price/' . $dateTimeYearAsString,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'price' => 4.2,
                'price_combined' => 6.4,
            ], JSON_THROW_ON_ERROR)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_OK, $response->getStatusCode());
        $this->assertSame(sprintf('{"message":"Price updated successfully","price":{"year":%d,"price":4.2,"price_combined":6.4}}', (int) $dateTimeYearAsString), $response->getContent());
    }

    public function testEditPriceWithNoPricesFound(): void
    {
        $dateTime = new DateTimeImmutable('now')->modify('-1 year');
        $dateTimeYearAsString = $dateTime->format('Y');
        $this->client->request('PUT', '/api/price/' . $dateTimeYearAsString,
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode([
                'price' => 4.2,
                'price_combined' => 6.4,
            ], JSON_THROW_ON_ERROR)
        );

        $response = $this->client->getResponse();
        $this->assertSame(Response::HTTP_NOT_FOUND, $response->getStatusCode());
        $this->assertSame('{"error":"1023: No price for this year found."}', $response->getContent());
    }
}
