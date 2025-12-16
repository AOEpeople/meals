<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Controller;

use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Event;
use App\Mealz\MealBundle\Entity\EventParticipation;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Repository\DayRepository;
use App\Mealz\MealBundle\Repository\MealRepositoryInterface;
use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\UserBundle\Entity\Role;
use App\Mealz\UserBundle\Repository\ProfileRepositoryInterface;
use App\Mealz\UserBundle\Repository\RoleRepositoryInterface;
use DateTime;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Order;
use Exception;
use Override;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\MockFileSessionStorage;

abstract class AbstractControllerTestCase extends AbstractDatabaseTestCase
{
    /**
     * Role based test users.
     */
    protected const string USER_STANDARD = 'alice.meals';
    protected const string USER_FINANCE = 'finance.meals';
    protected const string USER_KITCHEN_STAFF = 'kochomi.meals';

    protected KernelBrowser $client;

    #[Override]
    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    protected function loginAs(string $username): void
    {
        $repo = $this->client->getContainer()->get('doctrine')->getRepository(Profile::class);
        $user = $repo->find($username);

        if (!($user instanceof Profile)) {
            throw new RuntimeException($username . ': user not found');
        }

        $this->client->loginUser($user);
    }

    protected function getFormCSRFToken(string $uri, string $tokenFieldSelector): string
    {
        $this->client->xmlHttpRequest('GET', $uri);
        $htmlForm = $this->client->getResponse()->getContent();
        $crawler = new Crawler($htmlForm);
        $token = $crawler->filter($tokenFieldSelector)->attr('value');

        if ('' === $token || null === $token) {
            throw new RuntimeException('token fetch error, path: ' . $uri . ', fieldSelector: ' . $tokenFieldSelector);
        }

        return $token;
    }

    protected function getUserProfile(string $username): Profile
    {
        /** @var ProfileRepositoryInterface $profileRepository */
        $profileRepository = self::getContainer()->get(ProfileRepositoryInterface::class);
        $userProfile = $profileRepository->findOneBy(['username' => $username]);

        if (!($userProfile instanceof Profile)) {
            $this->fail('user profile not found: ' . $username);
        }

        return $userProfile;
    }

    protected function mockServices(array $options = []): void
    {
        $defaultOptions = ['mockFlashBag' => true];
        $options = array_merge($defaultOptions, $options);

        if ($options['mockFlashBag']) {
            $this->mockFlashBag();
        }
    }

    /**
     * Helper method to get a user role.
     *
     * @param string $roleType Role string identifier i.e. sid.
     */
    protected function getRole(string $roleType): Role
    {
        /** @var RoleRepositoryInterface $roleRepository */
        $roleRepository = self::getContainer()->get(RoleRepositoryInterface::class);
        $role = $roleRepository->findOneBy(['sid' => $roleType]);
        if (!($role instanceof Role)) {
            $this->fail('user role not found:  "' . $roleType);
        }

        return $role;
    }

    /**
     * Helper method to create a new user profile object.
     *
     * @param string $firstName User first name
     * @param string $lastName  User last name
     * @param string $company   User company
     */
    #[Override]
    protected function createProfile(string $firstName = '', string $lastName = '', string $company = ''): Profile
    {
        $firstName = ('' !== $firstName) ? $firstName : 'Test';
        $lastName = ('' !== $lastName) ? $lastName : 'User' . mt_rand();

        $profile = new Profile();
        $profile->setUsername($firstName . '.' . $lastName);
        $profile->setFirstName($firstName);
        $profile->setName($lastName);

        if ('' !== $company) {
            $profile->setCompany($company);
        }

        return $profile;
    }

    /**
     * Helper method to create a new participant object.
     */
    protected function createParticipant(Profile $profile, Meal $meal): Participant
    {
        $participant = new Participant($profile, $meal);
        $this->persistAndFlushAll([$participant]);

        return $participant;
    }

    protected function createEventParticipation(Day $day, Event $event): EventParticipation
    {
        $eventParticipation = new EventParticipation($day, $event);
        $day->addEvent($eventParticipation);
        $this->persistAndFlushAll([$eventParticipation, $day]);

        return $eventParticipation;
    }

    protected function createFutureEvent(): EventParticipation
    {
        $newEvent = $this->createEvent();
        $this->persistAndFlushAll([$newEvent]);

        $dayRepo = self::getContainer()->get(DayRepository::class);

        $criteria = new Criteria();
        $criteria->where(Criteria::expr()->gt('lockParticipationOn', new DateTime()));

        /** @var Day $day */
        $day = $dayRepo->matching($criteria)->get(0);
        $this->assertNotNull($day);

        return $this->createEventParticipation($day, $newEvent);
    }

    /**
     * Helper method to get the recent meal.
     */
    protected function getRecentMeal(?DateTime $dateTime = null): Meal
    {
        if (null === $dateTime) {
            $dateTime = new DateTime();
        }

        /** @var MealRepositoryInterface $mealRepository */
        $mealRepository = self::getContainer()->get(MealRepositoryInterface::class);
        $criteria = Criteria::create();
        $criteria
            ->where(Criteria::expr()->lte('dateTime', $dateTime))
            ->orderBy(['dateTime' => Order::Descending]);

        $meal = $mealRepository->matching($criteria)->first();
        if ($meal instanceof Meal) {
            return $meal;
        }

        throw new RuntimeException('test meal not found');
    }

    /**
     * @return Meal[]
     *
     * @throws Exception
     */
    protected function getLockedMeals(): array
    {
        /** @var MealRepositoryInterface $mealsRepo */
        $mealsRepo = self::getContainer()->get(MealRepositoryInterface::class);

        $meals = $mealsRepo->getLockedMeals();
        if (0 < count($meals)) {
            return $meals;
        }

        // got no locked meals, modify existing meals to make few available
        $criteria = new Criteria(Criteria::expr()->gt('dateTime', new DateTime()), ['dateTime' => Criteria::ASC]);
        $meals = $mealsRepo->matching($criteria);

        if (1 > count($meals)) {
            throw new Exception('could not fake locked meal');
        }

        $mealsDay = $meals->first()->getDay();
        $mealsDay->setLockParticipationDateTime(new DateTime('- 30 minutes'));
        $this->persistAndFlushAll([$mealsDay]);

        return $meals->toArray();
    }

    /**
     * Helper method to create a user transaction with specific amount and date.
     */
    protected function createTransactions(Profile $user, float $amount = 5.0, ?DateTime $date = null): void
    {
        $amount = filter_var(
            $amount,
            FILTER_VALIDATE_FLOAT,
            ['options' => ['min_range' => 0.1, 'default' => random_int(1000, 5000) / 100]]
        );
        $date = $date ?? new DateTime();

        $transaction = new Transaction();
        $transaction->setAmount($amount);
        $transaction->setProfile($user);
        $transaction->setDate($date);

        $this->persistAndFlushAll([$transaction]);
    }

    /**
     * mock the Flash Bag.
     */
    private function mockFlashBag(): void
    {
        // Mock session.storage for flash-bag
        $session = new Session(new MockFileSessionStorage());
        $this->client->getContainer()->set('session', $session);
    }

    protected function createFutureEmptyWeek(DateTime $date): void
    {
        $year = $date->format('o');
        $week = $date->format('W');
        $dishRepository = $this->getDoctrine()->getRepository(Dish::class);
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
                    "events": {},
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
                    "events": {},
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
                    "events": {},
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
                    "events": {},
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
                    "events": {
                        "3": {
                            "eventId": ' . $testEvent->getId() . ',
                            "eventSlug": "' . $testEvent->getSlug() . '",
                            "eventTitle": "' . $testEvent->getTitle() . '",
                            "isPublic": ' . ($testEvent->isPublic() ? 'true' : 'false') . '
                        }
                    },
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
