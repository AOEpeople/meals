<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\DataFixtures\ORM;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;
use RuntimeException;

class LoadParticipants extends Fixture implements OrderedFixtureInterface
{
    /**
     * Constant to declare load order of fixture.
     */
    private const ORDER_NUMBER = 9;

    protected ObjectManager $objectManager;

    /**
     * @var Meal[]
     */
    protected array $meals = [];

    /**
     * @var Profile[]
     */
    protected array $profiles = [];

    /**
     * @var Slot[]
     */
    protected array $slots = [];

    /**
     * @var array<string, Slot>
     */
    protected array $slotIndex = [];

    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $this->objectManager = $manager;
        $this->loadReferences();

        $this->loadSimpleMealParticipants();
        $this->loadCombinedMealParticipants();

        $this->objectManager->flush();
    }

    /**
     * @throws Exception
     */
    private function loadSimpleMealParticipants(): void
    {
        foreach ($this->meals as $meal) {
            $users = $this->getRandomUsers();

            foreach ($users as $user) {
                if ('finance.meals' === $user->getUsername()) {
                    continue;
                }

                $participant = new Participant($user, $meal);

                $participant->setCostAbsorbed(false);

                if ($meal->getLockDateTime() < new DateTime()) {
                    $participant->setOfferedAt(time());
                } else {
                    $participant->setOfferedAt(0);
                }

                $indexString = $user->getUsername() . '_' . $meal->getDay()->getId();

                if (!array_key_exists($indexString, $this->slotIndex)) {
                    $randomSlot = $this->getRandomActiveSlot();
                    $this->slotIndex[$indexString] = $randomSlot;
                    $participant->setSlot($randomSlot);
                } else {
                    $participant->setSlot($this->slotIndex[$indexString]);
                }

                $this->objectManager->persist($participant);
            }
        }
    }

    private function loadCombinedMealParticipants(): void
    {
        $username = 'bob.meals';
        $days = $this->getDaysWithDishVariations();

        foreach ($days as $day) {
            if (true === $this->hasCombinedMeal($day)) {
                $combinedMeal = $this->getCombinedMeal($day);
                $combinedMealDishes = $this->getRandomCombinedMealDishes($day);
                $profile = $this->getProfile($username);

                $participant = new Participant($profile, $combinedMeal);
                $participant->setCombinedDishes($combinedMealDishes);

                $indexString = $username . '_' . $day->getId();

                if (!array_key_exists($indexString, $this->slotIndex)) {
                    $randomSlot = $this->getRandomActiveSlot();
                    $this->slotIndex[$indexString] = $randomSlot;
                    $participant->setSlot($randomSlot);
                } else {
                    $participant->setSlot($this->slotIndex[$indexString]);
                }
                $this->objectManager->persist($participant);
            }
        }
    }

    /**
     * Get days when meals with dish variations are offered.
     *
     * @return Day[]
     *
     * @psalm-return list<Day>
     */
    private function getDaysWithDishVariations(): array
    {
        $days = [];

        foreach ($this->meals as $meal) {
            if ($meal->getDish() instanceof DishVariation) {
                $mealDay = $meal->getDay();
                $days[$mealDay->getId()] = $mealDay;
            }
        }

        return array_values($days);
    }

    private function hasCombinedMeal(Day $day): bool
    {
        foreach ($day->getMeals() as $meal) {
            if ($meal->isCombinedMeal()) {
                return true;
            }
        }

        return false;
    }

    private function getCombinedMeal(Day $day): Meal
    {
        foreach ($day->getMeals() as $meal) {
            if ($meal->isCombinedMeal()) {
                return $meal;
            }
        }

        throw new RuntimeException('no combined meal found on ' . $day->getDateTime()->format('Y-m-d'));
    }

    /**
     *  Returns a currently active Random Slot.
     */
    private function getRandomActiveSlot(): Slot
    {
        // only use active slots
        $slots = array_filter($this->slots, fn ($slot) => $slot->isEnabled() && !$slot->isDeleted());

        return $slots[array_rand($slots)];
    }

    /**
     * @return Dish[]
     *
     * @psalm-return list<Dish>
     */
    private function getRandomCombinedMealDishes(Day $day): array
    {
        $dishes = [];
        $opts = [];

        foreach ($day->getMeals() as $meal) {
            if ($meal->isCombinedMeal()) {
                continue;
            }

            $dish = $meal->getDish();
            if ($dish instanceof DishVariation) {
                $opts[$dish->getParent()->getId()][] = $dish;
            } else {
                $opts[$dish->getId()][] = $dish;
            }
        }

        if (2 > count($opts)) {
            throw new RuntimeException(
                sprintf(
                    'insufficient dishes on %s; required: 2, got: %d',
                    $day->getDateTime()->format('Y-m-d'),
                    count($opts)
                )
            );
        }

        foreach (array_slice($opts, 0, 2) as $opt) {
            if (1 < count($opt)) {
                $randKey = array_rand($opt);
                $dishes[] = $opt[$randKey];
            } else {
                $dishes[] = $opt[0];
            }
        }

        return $dishes;
    }

    public function getOrder(): int
    {
        // load as eight
        return self::ORDER_NUMBER;
    }

    protected function loadReferences(): void
    {
        foreach ($this->referenceRepository->getReferences() as $referenceName => $reference) {
            if ($reference instanceof Meal) {
                // we can't just use $reference here, because
                // getReference() does some doctrine magic that getReferences() does not
                $this->meals[] = $this->getReference($referenceName);
            } elseif ($reference instanceof Profile) {
                $this->profiles[] = $this->getReference($referenceName);
            } elseif ($reference instanceof Slot) {
                $this->slots[] = $this->getReference($referenceName);
            }
        }
    }

    /**
     * @return Profile[]
     *
     * @psalm-return list<Profile>
     *
     * @throws Exception
     */
    protected function getRandomUsers(): array
    {
        $number = random_int(0, count($this->profiles));
        $users = [];

        if ($number > 1) {
            foreach (array_rand($this->profiles, $number) as $userKey) {
                $users[] = $this->profiles[$userKey];
            }
        } elseif (1 === $number) {
            $users[] = $this->profiles[array_rand($this->profiles)];
        }

        return $users;
    }

    private function getProfile(string $username): Profile
    {
        foreach ($this->profiles as $profile) {
            if ($username === $profile->getUsername()) {
                return $profile;
            }
        }

        throw new RuntimeException($username . ': profile not found');
    }
}
