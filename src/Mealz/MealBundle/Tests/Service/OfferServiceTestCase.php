<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service;

use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\MealCollection;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Repository\DishRepository;
use App\Mealz\MealBundle\Service\CombinedMealService;
use App\Mealz\MealBundle\Service\OfferService;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\Common\Collections\Collection;

class OfferServiceTestCase extends AbstractParticipationServiceTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadRoles(),
            new LoadUsers(static::getContainer()->get('security.user_password_hasher')),
        ]);

        /* https://stackoverflow.com/questions/73209831/unitenum-cannot-be-cast-to-string */
        $price = self::$kernel->getContainer()->getParameter('mealz.meal.combined.price');
        $price = is_float($price) ? $price : 0;

        $dishRepo = static::getContainer()->get(DishRepository::class);
        $this->cms = new CombinedMealService($price, $this->entityManager, $dishRepo);
        $this->offerService = new OfferService($this->participantRepo);
    }

    /**
     * @test
     *
     * @testdox No offers for open (not locked and not expired) meal
     */
    public function noOffersForOpenMeal(): void
    {
        $meal = $this->getMeal();

        $offers = $this->offerService->getOffers($meal);

        $this->assertEmpty($offers);
    }

    /**
     * @test
     *
     * @testdox No offers for locked but not expired meal without an offerer
     */
    public function noOffersForLockedMeal(): void
    {
        $meal = $this->getMeal(true);

        $offers = $this->offerService->getOffers($meal);

        $this->assertEmpty($offers);
    }

    /**
     * @test
     *
     * @testdox No offers for locked and expired meal without an offerer
     */
    public function noOffersExpiredMeal(): void
    {
        $meal = $this->getMeal(true, true); // same as $this->getMeal(false, true) – if a meal is expired, it's also locked

        $offers = $this->offerService->getOffers($meal);

        $this->assertEmpty($offers);
    }

    /**
     * @test
     *
     * @testdox No offers for locked and not expired meal with usual participant
     */
    public function noOffersForUsualParticipant(): void
    {
        $offerer = $this->getProfile('bob.meals');
        $meal = $this->getMeal(true, false, [$offerer], false);

        $offers = $this->offerService->getOffers($meal);

        $this->assertEmpty($offers);
    }

    /**
     * @test
     *
     * @testdox Get offers for locked and not expired meal with an offerer
     */
    public function offersFromOfferer(): void
    {
        $offerer = $this->getProfile('bob.meals');
        $meal = $this->getMeal(true, false, [$offerer]);

        $offers = $this->offerService->getOffers($meal);

        $dishSlug = $meal->getDish()->getSlug();

        $this->assertArrayHasKey($dishSlug, $offers);

        $this->assertEquals($dishSlug, $offers[$meal->getDish()->getSlug()]['id']);

        $this->assertEquals(1, $offers[$meal->getDish()->getSlug()]['count']);

        $this->assertEmpty($offers[$meal->getDish()->getSlug()]['dishes']);
    }

    /**
     * @test
     *
     * @testdox Get offers for locked and not expired meal with multiple offerers
     */
    public function offersFromOfferers(): void
    {
        $offerers = [
            $this->getProfile('bob.meals'),
            $this->getProfile('john.meals'),
        ];
        $meal = $this->getMeal(true, false, $offerers);

        $offers = $this->offerService->getOffers($meal);

        $dishSlug = $meal->getDish()->getSlug();

        $this->assertArrayHasKey($dishSlug, $offers);

        $this->assertEquals($dishSlug, $offers[$meal->getDish()->getSlug()]['id']);

        $this->assertEquals(2, $offers[$meal->getDish()->getSlug()]['count']);

        $this->assertEmpty($offers[$meal->getDish()->getSlug()]['dishes']);
    }

    /**
     * @test
     *
     * @testdox No offers for combined meal if there is no offerer
     */
    public function noOffersForCombinedMealOffers(): void
    {
        $meals = $this->createAndCheckMealCollection();
        $combinedMeal = $this->getCombinedMeal($meals);

        $offers = $this->offerService->getOffers($combinedMeal);
        $this->assertEmpty($offers);
    }

    /**
     * @test
     *
     * @testdox Get offer for combined meal if there is an offerer
     */
    public function combinedMealOffersFromOfferer(): void
    {
        $meals = $this->createAndCheckMealCollection();
        $combinedMeal = $this->getCombinedMeal($meals);

        $profiles[] = $this->getProfile('bob.meals');
        $bookedDishes = [];
        foreach ($meals as $meal) {
            $bookedDishes[] = $meal->getDish();
        }

        $offerers = $this->addCombinedMealOfferers($combinedMeal, $profiles, $bookedDishes);
        $this->assertCount(count($profiles), $offerers);

        $offers = $this->offerService->getOffers($combinedMeal);

        $participant = $offerers[0];
        $combinedDishSlugs = null;
        foreach ($participant->getCombinedDishes() as $dish) {
            $combinedDishSlugs[] = $dish->getSlug();
        }
        sort($combinedDishSlugs, SORT_NATURAL);
        $combinationID = implode(',', $combinedDishSlugs);
        $this->assertArrayHasKey($combinationID, $offers);

        $offer = $offers[$combinationID];
        $this->assertEquals($combinationID, $offer['id']);
        $this->assertEquals(count($offerers), $offer['count']);

        $this->assertCount($participant->getCombinedDishes()->count(), $offer['dishes']);
        foreach ($offer['dishes'] as $offeredDish) {
            $dish = $bookedDishes[0]->getSlug() === $offeredDish['slug'] ? $bookedDishes[0] : $bookedDishes[1];
            $this->assertEquals($dish->getTitle(), $offeredDish['title']);
        }
    }

    /**
     * @test
     *
     * @testdox Get offers for combined meal if there are multiple offerers
     */
    public function combinedMealOffersFromOfferers(): void
    {
        $meals = $this->createAndCheckMealCollection();
        $combinedMeal = $this->getCombinedMeal($meals);

        $profiles[] = $this->getProfile('bob.meals');
        $profiles[] = $this->getProfile('john.meals');
        $bookedDishes = [];
        foreach ($meals as $meal) {
            $bookedDishes[] = $meal->getDish();
        }
        $offerers = $this->addCombinedMealOfferers($combinedMeal, $profiles, $bookedDishes);
        $this->assertCount(count($profiles), $offerers);

        $offers = $this->offerService->getOffers($combinedMeal);

        $combinationID = null;
        /** @var Participant $offerer */
        foreach ($offerers as $offerer) {
            $combinedDishSlugs = null;
            foreach ($offerer->getCombinedDishes() as $dish) {
                $combinedDishSlugs[] = $dish->getSlug();
            }
            sort($combinedDishSlugs, SORT_NATURAL);
            $combinationID = implode(',', $combinedDishSlugs);
            $this->assertArrayHasKey($combinationID, $offers);
        }

        $offer = $offers[$combinationID];
        $this->assertEquals($combinationID, $offer['id']);
        $this->assertEquals(count($offerers), $offer['count']);

        $this->assertCount($offerers[0]->getCombinedDishes()->count(), $offer['dishes']);
        foreach ($offer['dishes'] as $offeredDish) {
            $dish = $bookedDishes[0]->getSlug() === $offeredDish['slug'] ? $bookedDishes[0] : $bookedDishes[1];
            $this->assertEquals($dish->getTitle(), $offeredDish['title']);
        }
    }

    /**
     * @test
     *
     * @testdox Get offers for combined meal with variations if there are multiple offerers
     */
    public function combinedMealOffersWithVariationsFromOfferers(): void
    {
        $meals = new MealCollection([
            $this->getMeal(true),
        ]);

        $dishVariations = $this->createDishWithVariations();
        /** @var DishVariation $dishVariation */
        foreach ($dishVariations as $dishVariation) {
            $meals->add($this->getMeal(true, false, [], true, $dishVariation));
        }

        $date = null;
        /** @var Meal $meal */
        foreach ($meals as $meal) {
            if (null === $date) {
                $date = $meal->getDateTime()->format('Y-m-d');
                continue;
            }

            // Check if meals are on the same day
            $this->assertEquals($date, $meal->getDateTime()->format('Y-m-d'));
        }

        $combinedMeal = $this->getCombinedMeal($meals);

        $profiles[] = $this->getProfile('alice.meals');
        $profiles[] = $this->getProfile('jane.meals');
        $offerers = [];
        $bookedCombinations = [];
        /**
         * @var int $idx
         * @var Profile $profile
         */
        foreach ($profiles as $idx => $profile) {
            $bookedDishes = [
                $meals[0]->getDish(),
                $meals[$idx + 1]->getDish(),
            ];

            $offerers = array_merge($offerers, $this->addCombinedMealOfferers($combinedMeal, [$profile], $bookedDishes));
            $bookedCombinations[] = $bookedDishes;
        }

        $this->assertCount(count($profiles), $offerers);

        $offers = $this->offerService->getOffers($combinedMeal);

        /** @var Participant $offerer */
        foreach ($offerers as $idx => $offerer) {
            $combinedDishSlugs = [];
            foreach ($offerer->getCombinedDishes() as $dish) {
                $combinedDishSlugs[] = $dish->getSlug();
            }
            sort($combinedDishSlugs, SORT_NATURAL);
            $combinationID = implode(',', $combinedDishSlugs);
            $this->assertArrayHasKey($combinationID, $offers);

            $offer = $offers[$combinationID];

            $this->assertEquals($combinationID, $offer['id']);
            $this->assertEquals(1, $offer['count']);

            $this->assertCount($offerer->getCombinedDishes()->count(), $offer['dishes']);

            $bookedDishes = $bookedCombinations[$idx];
            foreach ($offer['dishes'] as $offeredDish) {
                $dish = $bookedDishes[0]->getSlug() === $offeredDish['slug'] ? $bookedDishes[0] : $bookedDishes[1];
                $this->assertEquals($dish->getTitle(), $offeredDish['title']);
            }
        }
    }

    private function createAndCheckMealCollection(): MealCollection
    {
        $meals = new MealCollection([
            $this->getMeal(true),
            $this->getMeal(true),
        ]);
        $this->assertEquals($meals[0]->getDateTime(), $meals[1]->getDateTime());

        return $meals;
    }

    private function createDishWithVariations(): Collection
    {
        $parentDish = $this->createDish();
        $dishVariations = $parentDish->getVariations();
        $dishVariationA = $this->createDishVariation($parentDish);
        $dishVariationB = $this->createDishVariation($parentDish);
        $dishVariations->add($dishVariationA);
        $dishVariations->add($dishVariationB);
        $dishes = $dishVariations->toArray();
        $dishes[] = $parentDish;
        $this->persistAndFlushAll($dishes);

        return $dishVariations;
    }

    private function addCombinedMealOfferers(Meal $combinedMeal, array $profiles, array $bookedDishes): array
    {
        $participants = [];
        foreach ($profiles as $profile) {
            $participant = new Participant($profile, $combinedMeal);
            $participant->setOfferedAt(time());
            $participant->setCombinedDishes($bookedDishes);

            $participants[] = $participant;
        }

        $this->persistAndFlushAll($participants);
        $this->entityManager->refresh($combinedMeal);

        return $participants;
    }

    protected function validateParticipant(Participant $participant, Profile $profile, Meal $meal, ?Slot $slot = null)
    {
        echo 'not implemented.';
    }
}
