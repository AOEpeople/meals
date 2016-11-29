<?php

namespace Mealz\MealBundle\Tests\Controller;

use Doctrine\Common\Collections\Criteria;
use Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use Mealz\MealBundle\Entity\GuestInvitation;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\Service\Doorman;
use Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use Mealz\UserBundle\Entity\Profile;

/**
 * Meal controller test.
 *
 * @author Chetan Thapliyal <chetan.thapliyal@aoe.com>
 */
class MealControllerTest extends AbstractControllerTestCase
{
    /**
     * Prepares test environment.
     */
    public function setUp()
    {
        parent::setUp();

        $this->createDefaultClient();
        $this->clearAllTables();
        $this->loadFixtures([
            new LoadCategories(),
            new LoadWeeks(),
            new LoadDays(),
            new LoadDishes(),
            new LoadMeals(),
            new LoadRoles(),
            new LoadUsers($this->client->getContainer()),
        ]);
    }

    /**
     * @test
     * @dataProvider getGuestEnrollmentData
     *
     * @param string $firstName        Guest first name
     * @param string $lastName         Guest last name
     * @param string $company          Guest company name
     * @param string $selectDish       Flag whether or not to select a dish
     * @param bool   $enrollmentStatus Flag whether enrollment should be successful or not.
     */
    public function enrollAsGuest($firstName, $lastName, $company, $selectDish, $enrollmentStatus)
    {
        $userProfile = $this->getUserProfile();
        $meal = $this->getAvailableMeal();

        // Create guest invitation link
        $guestInvitation = new GuestInvitation($userProfile, $meal->getDay());
        $this->persistAndFlushAll([$guestInvitation]);

        // Enroll as guest
        $guestEnrollmentUrl = '/menu/guest/'.$guestInvitation->getId();
        $crawler = $this->client->request('GET', $guestEnrollmentUrl);
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        $form = $crawler->filterXPath('//form[@name="invitation_form"]')->form([
            'invitation_form[profile][name]' => $lastName,
            'invitation_form[profile][firstName]' => $firstName,
            'invitation_form[profile][company]' => $company,
        ]);

        if ($selectDish) {
            $form['invitation_form[day][meals]'][0]->tick();
        }

        $this->client->submit($form);

        // Verify enrollment is successful
        $mealParticipants = $this->getMealParticipants($meal);

        /** @var Participant $participant */
        foreach ($mealParticipants as $participant) {
            $profile = $participant->getProfile();

            if ($firstName === $profile->getFirstName()
                && ($lastName === $profile->getName())
                && ($company === $profile->getCompany())
                && $profile->isGuest()) {
                $this->assertTrue($enrollmentStatus);

                return;
            }
        }

        $this->assertFalse($enrollmentStatus);
    }

    /**
     * @return array
     */
    public function getGuestEnrollmentData()
    {
        $time = time();

        return [
            // [FirstName, LastName, Company, Select Dish, Enrollment Status]
            ['Max01:'.$time, 'Mustermann01'.$time, 'Test Comapany01'.$time, false, false],
            ['', 'Mustermann02'.$time, 'Test Comapany02'.$time, true, false],
            ['Max03:'.$time, '', 'Test Comapany03'.$time, true, false],
            ['Max04:'.$time, 'Mustermann04'.$time, '', true, false],
            ['Max05:'.$time, 'Mustermann05'.$time, 'Test Comapany05'.$time, true, true],
        ];
    }

    /**
     * Gets the next available meal.
     *
     * @return Meal
     */
    private function getAvailableMeal()
    {
        $availableMeal = null;

        /** @var \Mealz\MealBundle\Entity\MealRepository $mealRepository */
        $mealRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Meal');
        $criteria = Criteria::create();
        $meals = $mealRepository->matching($criteria->where(Criteria::expr()->gte('dateTime', new \DateTime())));

        if ($meals->count()) {
            /** @var Doorman $doorman */
            $doorman = $this->client->getContainer()->get('mealz_meal.doorman');
            foreach ($meals as $meal) {
                if ($doorman->isToggleParticipationAllowed($meal->getDateTime())) {
                    $availableMeal = $meal;
                    break;
                }
            }
        }

        if (!$availableMeal) {
            $this->fail('No test meal found.');
        }

        return $availableMeal;
    }

    /**
     * Gets all the participants for a meal.
     *
     * @param  Meal $meal       Meal instance
     * @return array
     */
    private function getMealParticipants($meal)
    {
        /** @var \Mealz\MealBundle\Entity\ParticipantRepository $participantRepository */
        $participantRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Participant');
        $participants = $participantRepository->findBy(['meal' => $meal->getId()]);

        return $participants;
    }
}
