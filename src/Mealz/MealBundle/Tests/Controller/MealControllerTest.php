<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Controller;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\GuestInvitation;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Repository\MealRepositoryInterface;
use App\Mealz\MealBundle\Repository\ParticipantRepository;
use App\Mealz\MealBundle\Service\Doorman;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use DateTime;
use Doctrine\Common\Collections\Criteria;
use Override;

final class MealControllerTest extends AbstractControllerTestCase
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

    /**
     * Tests the acceptOffer action (accepting a meal offer) in the meal controller.
     * First case: An user accepts an available offer.
     */
    public function testAcceptAvailableOffer(): void
    {
        $this->markTestSkipped('frontend test');
        $this->loginAs(self::USER_STANDARD);

        // create a test profile
        $profile = $this->createProfile('Max', 'Mustermann' . time());
        $this->persistAndFlushAll([$profile]);

        // get first locked meal and make it an available offer
        $lockedMeals = $this->getLockedMeals();
        $firstLockedMeal = $lockedMeals[0];
        $participant = $this->createParticipant($profile, $firstLockedMeal);
        $participant->setOfferedAt(time());
        $this->persistAndFlushAll([$participant]);

        // variables for first case
        $date = date_format($firstLockedMeal->getDateTime(), 'Y-m-d');
        $dish = $firstLockedMeal->getDish()->getSlug();

        // first case: accept available offer
        $this->client->request('GET', '/menu/' . $date . '/' . $dish . '/accept-offer');
        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'accepting offer failed');
    }

    /**
     * Second case: There are two offers and the user accepts one and automatically takes the one, that was offered earlier.
     */
    public function testAcceptFirstOffer(): void
    {
        $this->markTestSkipped('frontend test');
        $this->loginAs(self::USER_STANDARD);

        // create a test profile
        $profile = $this->createProfile('Max', 'Mustermann' . time());

        // create second test profile
        $secondProfile = $this->createProfile('Meike', 'Musterfrau' . time());
        $this->persistAndFlushAll([$profile, $secondProfile]);

        // get first locked meal and make it an available offer
        $lockedMealsArray = $this->getLockedMeals();
        $lockedMeal = $lockedMealsArray[0];
        $participant = $this->createParticipant($profile, $lockedMeal);
        $participant->setOfferedAt(time());

        // create second participant for same locked meal and make it an available offer (which was offered after the first one)
        $secondParticipant = $this->createParticipant($secondProfile, $lockedMeal);
        $secondParticipant->setOfferedAt(time() + 1);

        $this->persistAndFlushAll([$participant, $secondParticipant]);

        // variables for first case
        $date = date_format($lockedMeal->getDateTime(), 'Y-m-d');
        $dish = $lockedMeal->getDish()->getSlug();

        // first case: accept available offer
        $this->client->request('GET', '/menu/' . $date . '/' . $dish . '/accept-offer');
        $this->assertTrue($this->client->getResponse()->isSuccessful(), 'accepting offer failed');

        // verification by checking the database
        $newParticipant = $this->getDoctrine()->getRepository(Participant::class)->find($participant->getId());
        $this->assertTrue(0 === $newParticipant->getOfferedAt());

        // second case: check if second offer is still available
        $secondOffer = $this->getDoctrine()->getRepository(Participant::class)->find($secondParticipant->getId());
        $this->assertTrue(0 != $secondOffer->getOfferedAt(), 'second offer was taken');
    }

    /**
     * Third case: An user tries to accept an outdated offer.
     */
    public function testAcceptOutdatedOffer(): void
    {
        $this->markTestSkipped('frontend test');
        $this->loginAs(self::USER_STANDARD);

        // create a test profile
        $profile = $this->createProfile('Max', 'Mustermann' . time());
        $this->persistAndFlushAll([$profile]);

        // variables for third case
        $mealsRepo = self::$container->get(MealRepositoryInterface::class);
        $outdatedMealsArray = $mealsRepo->getOutdatedMeals();
        $outdatedMeal = $outdatedMealsArray[0];

        $date = date_format($outdatedMeal->getDateTime(), 'Y-m-d');
        $dish = $outdatedMeal->getDish();

        // third case: accepting outdated offer
        $this->client->request('GET', '/menu/' . $date . '/' . $dish . '/accept-offer');
        $statusCode = $this->client->getResponse()->getStatusCode();
        $this->assertGreaterThanOrEqual(403, $statusCode, 'user accepted outdated offer');
        $this->assertLessThanOrEqual(404, $statusCode, 'user accepted outdated offer');
    }

    /**
     * Testing joining Meal with variations.
     * We have next situation: (1 Dish without variations and 1 Dish with 2 variations)
     * If we can subscribe to all 3 of these options then you can select Dish with and without variations.
     *
     * /menu/{date}/{dish}/join/{profile}
     */
    public function testJoinAMealWithVariations(): void
    {
        $this->markTestSkipped('frontend test');
        // data provider method
        $dataProvider = $this->getJoinAMealData();
        $userProfile = $this->getUserProfile(self::USER_STANDARD);
        $username = $this->getUserProfile(self::USER_STANDARD)->getUsername();

        // load a home page
        $this->client->request('GET', '/');
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        // go through provided data and test functionality
        foreach ($dataProvider as $dataRow) {
            // Call controller actionxxxx
            $slug = $dataRow[1]->getDish()->getSlug();
            $this->client->request('GET', "/menu/$dataRow[0]/$slug/join/$username");

            // Verify if enrollment is successful
            $mealParticipants = $this->getMealParticipants($dataRow[1]);

            foreach ($mealParticipants as $participant) {
                $profile = $participant->getProfile();

                if ($userProfile->getFirstName() === $profile->getFirstName()
                    && ($userProfile->getName() === $profile->getName())
                    && ($userProfile->getUsername() === $profile->getUsername())
                ) {
                    $this->assertTrue(true);

                    break;
                }
                $this->fail();
            }
        }
    }

    /**
     * Searching a Day with 3 options. I adapted fixtures so we always have 1 day with 3 options
     * (1 Dish without variations and 1 Dish with 2 variations).
     *
     * @return (Meal|string)[][]
     *
     * @psalm-return list<array{0: string, 1: Meal}>
     */
    private function getJoinAMealData(): array
    {
        $mealRepository = self::getContainer()->get(MealRepositoryInterface::class);
        $meals = $mealRepository->getMealsOnADayWithVariationOptions();

        $dataProvider = [];
        foreach ($meals as $mealItem) {
            /** @var Meal $meal */
            $meal = $mealRepository->find($mealItem['id']);
            $dataProvider[] = [date('Y-m-d', $meal->getDay()->getDateTime()->getTimestamp()), $meal];
        }

        // in format [Date, Meal]
        return $dataProvider;
    }

    /**
     * @dataProvider getGuestEnrollmentData
     *
     * @param bool $enrollmentStatus flag whether enrollment should be successful or not
     */
    public function testEnrollAsGuest(
        string $firstName, string $lastName, string $company, bool $selectDish, bool $enrollmentStatus
    ): void {
        $this->markTestSkipped('frontend test');
        $userProfile = $this->getUserProfile(self::USER_STANDARD);
        $meal = $this->getAvailableMeal();

        // Create guest invitation link
        $guestInvitation = new GuestInvitation($userProfile, $meal->getDay());
        $this->persistAndFlushAll([$guestInvitation]);

        // Enroll as guest
        $guestEnrollmentUrl = '/menu/guest/' . $guestInvitation->getId();
        $crawler = $this->client->request('GET', $guestEnrollmentUrl);
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $form = $crawler->filterXPath('//form[@name="invitation_form"]')->form(
            [
                'invitation_form[profile][name]' => $lastName,
                'invitation_form[profile][firstName]' => $firstName,
                'invitation_form[profile][company]' => $company,
            ]
        );

        if ($selectDish) {
            $form['invitation_form[day][meals]'][0]->tick();
        }

        $this->client->submit($form, []);

        // Verify enrollment is successful
        $mealParticipants = $this->getMealParticipants($meal);

        /** @var Participant $participant */
        foreach ($mealParticipants as $participant) {
            $profile = $participant->getProfile();

            if ($firstName === $profile->getFirstName()
                && ($lastName === $profile->getName())
                && ($company === $profile->getCompany())
                && $profile->isGuest()
            ) {
                $this->assertTrue($enrollmentStatus);
            } else {
                $this->assertFalse($enrollmentStatus);
            }
        }
    }

    /**
     * @return (bool|string)[][]
     *
     * @psalm-return array{0: array{0: string, 1: string, 2: string, 3: false, 4: false}, 1: array{0: '', 1: string, 2: string, 3: true, 4: false}, 2: array{0: string, 1: '', 2: string, 3: true, 4: false}, 3: array{0: string, 1: string, 2: '', 3: true, 4: true}, 4: array{0: string, 1: string, 2: string, 3: true, 4: true}}
     */
    public function getGuestEnrollmentData(): array
    {
        $time = time();

        return [
            // [FirstName, LastName, Company, Select Dish, Enrollment Status]
            ['Max01:' . $time, 'Mustermann01' . $time, 'Test Comapany01' . $time, false, false],
            ['', 'Mustermann02' . $time, 'Test Comapany02' . $time, true, false],
            ['Max03:' . $time, '', 'Test Comapany03' . $time, true, false],
            ['Max04:' . $time, 'Mustermann04' . $time, '', true, true], // allow empty company
            ['Max05:' . $time, 'Mustermann05' . $time, 'Test Comapany05' . $time, true, true],
        ];
    }

    private function getAvailableMeal(): Meal
    {
        $availableMeal = null;

        $mealRepository = self::getContainer()->get(MealRepositoryInterface::class);
        $criteria = Criteria::create();
        $meals = $mealRepository->matching($criteria->where(Criteria::expr()->gte('dateTime', new DateTime())));

        if ($meals->count() > 0) {
            /** @var Doorman $doorman */
            $doorman = self::getContainer()->get('mealz_meal.doorman');
            foreach ($meals as $meal) {
                if ($doorman->isToggleParticipationAllowed($meal->getDateTime())) {
                    $availableMeal = $meal;
                    break;
                }
            }
        }

        if (null === $availableMeal) {
            $this->fail('No test meal found.');
        }

        return $availableMeal;
    }

    /**
     * @testdox A New dish is rendered with a "New meal" tag on home page.
     */
    public function testNewMealFlag(): void
    {
        $this->markTestSkipped('frontend test');
        $dish = new Dish();
        $dish->setTitleEn('Very Yummy Dish');
        $dish->setTitleDe('Sehr leckeres Gericht');

        $meal = $this->getAvailableMeal();
        $this->assertInstanceOf(Meal::class, $meal);

        $meal->setDish($dish);
        $this->persistAndFlushAll([$dish, $meal]);

        $crawler = $this->client->request('GET', '/');
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $flag = $crawler->filterXPath('//span[@class="new-flag"]')->getNode(0);

        if (null === $flag) {
            $this->fail('Flag not found');
        }
    }

    /**
     * @return Participant[]
     */
    private function getMealParticipants(Meal $meal): array
    {
        /** @var ParticipantRepository $participantRepo */
        $participantRepo = $this->getDoctrine()->getRepository(Participant::class);

        return $participantRepo->findBy(['meal' => $meal->getId()]);
    }
}
