<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Controller;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Repository\MealRepositoryInterface;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\MealBundle\Repository\WeekRepositoryInterface;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\UserBundle\Entity\Role;
use App\Mealz\UserBundle\Repository\ProfileRepositoryInterface;
use DateTime;
use Override;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

final class ParticipantControllerTest extends AbstractControllerTestCase
{
    protected static $participantFirstName;
    protected static $participantLastName;
    protected static $guestFirstName;
    protected static $guestLastName;
    protected static $guestCompany;
    protected static $userFirstName;
    protected static $userLastName;
    protected static $meal;

    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadCategories(),
            new LoadWeeks(),
            new LoadDays(),
            new LoadDishes(),
            new LoadDishVariations(),
            new LoadMeals(),
            new LoadRoles(),
            new LoadUsers(self::getContainer()->get('security.user_password_hasher')),
        ]);

        $this->loginAs(self::USER_KITCHEN_STAFF);

        $time = time();

        $dateTime = new DateTime('today 23:59:59');
        self::$meal = $this->getRecentMeal($dateTime);

        // Create profile for participant
        self::$participantFirstName = 'Max';
        self::$participantLastName = 'Mustermann' . $time;
        $participant = $this->createEmployeeProfileAndParticipation(
            self::$participantFirstName,
            self::$participantLastName,
            self::$meal
        );

        // Create profile for guest participant
        self::$guestFirstName = 'Jon';
        self::$guestLastName = 'Doe' . $time;
        self::$guestCompany = 'Company';
        $guestParticipant = $this->createGuestProfileAndParticipation(
            self::$guestFirstName,
            self::$guestLastName,
            self::$guestCompany,
            self::$meal
        );

        // Create profile for user (non participant)
        self::$userFirstName = 'Karl';
        self::$userLastName = 'Schmidt' . $time;
        $user = $this->createProfile('1', self::$userFirstName, self::$userLastName);
        $this->persistAndFlushAll([$user]);

        // Check that created profiles are persisted
        if (($this->getUserProfile($participant->getProfile()->getId()) instanceof Profile) === false) {
            $this->fail('Test participant not found.');
        }
        if (($this->getUserProfile($guestParticipant->getProfile()->getId()) instanceof Profile) === false) {
            $this->fail('Test guest not found.');
        }
        if (($this->getUserProfile($user->getId()) instanceof Profile) === false) {
            $this->fail('Test user not found.');
        }
    }

    public function testGetParticipationsForWeek(): void
    {
        $date = new DateTime('today 23:59:59');
        $week = (int) $date->format('W');

        $weekRepository = $this->getDoctrine()->getRepository(Week::class);
        $weekEntity = $weekRepository->findOneBy([
            'year' => $date->format('o'),
            'calendarWeek' => $week,
        ]);
        $this->assertNotNull($weekEntity);

        $this->client->request('GET', '/api/participations/' . $weekEntity->getId());
        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        foreach ($weekEntity->getDays() as $day) {
            $this->assertArrayHasKey($day->getId(), $responseData);
        }
    }

    public function testAddParticipant(): void
    {
        $mealRepo = self::getContainer()->get(MealRepositoryInterface::class);

        $profileToAdd = $this->getUserProfileByUsername(self::USER_STANDARD);
        $mealToAdd = $mealRepo->getFutureMeals()[0];
        $this->assertNotNull($mealToAdd);

        $routeStr = '/api/participation/' . (string) $profileToAdd->getId() . '/' . $mealToAdd->getId();
        $this->client->request('PUT', $routeStr);

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);

        $this->assertEquals($profileToAdd->getUsername(), $responseData['profile']);
        $this->assertEquals($mealToAdd->getDay()->getId(), $responseData['day']);
        $this->assertEquals($mealToAdd->getId(), $responseData['booked'][0]['mealId']);
    }

    public function testRemoveParticipant(): void
    {
        $this->client->catchExceptions(false);
        $participantRepo = self::getContainer()->get(ParticipantRepositoryInterface::class);
        $mealRepo = self::getContainer()->get(MealRepositoryInterface::class);
        $meal = $mealRepo->getFutureMeals()[0];
        $profile = $this->getUserProfileByUsername(self::USER_STANDARD);

        $participantToRemove = self::createParticipant($profile, $meal);
        $this->assertNotNull($participantRepo->findOneBy(['id' => $participantToRemove->getId()]));

        $routeStr = '/api/participation/' . (string) $profile->getId() . '/' . $meal->getId();
        $this->client->request('DELETE', $routeStr);

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $this->assertNull($participantRepo->findOneBy(['id' => $participantToRemove->getId()]));
    }

    public function testGetProfilesWithoutParticipation(): void
    {
        $date = new DateTime();
        $date->modify('next monday');

        $weekRepository = $this->getDoctrine()->getRepository(Week::class);
        $weekEntity = $weekRepository->findOneBy([
            'year' => $date->format('o'),
            'calendarWeek' => (int) $date->format('W'),
        ]);
        $this->assertNotNull($weekEntity);

        $routeStr = '/api/participations/' . $weekEntity->getId() . '/abstaining';
        $this->client->request('GET', $routeStr);

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $responseData = json_decode($response->getContent(), true);

        $this->assertTrue(is_array($responseData));
        $this->assertNotEmpty($responseData);

        $profileToParticipate = $responseData[0]['user'];
        $profileRepo = self::getContainer()->get(ProfileRepositoryInterface::class);
        $profile = $profileRepo->findOneBy(['username' => $profileToParticipate]);
        $meal = $weekEntity->getDays()[0]->getMeals()[0];
        self::createParticipant($profile, $meal);

        $routeStr = '/api/participations/' . $weekEntity->getId() . '/abstaining';
        $this->client->request('GET', $routeStr);

        $response = $this->client->getResponse();
        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $found = false;
        $responseData = json_decode($response->getContent(), true);
        foreach ($responseData as $part) {
            if ($part['user'] === $profileToParticipate) {
                $found = true;
                break;
            }
        }
        $this->assertFalse($found);
    }

    /**
     * return crawler for the current week participations.
     */
    protected function getCurrentWeekParticipations(): Crawler
    {
        $currentWeek = $this->getCurrentWeek();
        $crawler = $this->client->request('GET', '/participations/' . $currentWeek->getId() . '/edit');
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        return $crawler;
    }

    /**
     * return current week object.
     */
    protected function getCurrentWeek(): ?Week
    {
        /** @var WeekRepositoryInterface $weekRepository */
        $weekRepository = self::getContainer()->get(WeekRepositoryInterface::class);

        return $weekRepository->getCurrentWeek();
    }

    /**
     * create profile for user and add participation.
     */
    protected function createEmployeeProfileAndParticipation(
        string $userFirstName,
        string $userLastName,
        Meal $meal
    ): Participant {
        $user = $this->createProfile(uniqid(), $userFirstName, $userLastName);
        $this->persistAndFlushAll([$user]);
        $participant = new Participant($user, $meal);

        $this->persistAndFlushAll([$participant]);

        return $participant;
    }

    /**
     * create profile for guest and add participation.
     */
    protected function createGuestProfileAndParticipation(
        string $guestFirstName,
        string $guestLastName,
        string $guestCompany,
        Meal $meal
    ): Participant {
        $user = $this->createProfile(uniqid(), $guestFirstName, $guestLastName, $guestCompany);
        $user->addRole($this->getRole(Role::ROLE_GUEST));
        $this->persistAndFlushAll([$user]);
        $participant = new Participant($user, $meal);

        $this->persistAndFlushAll([$participant]);

        return $participant;
    }
}
