<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\DataFixtures\ORM;

use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\UserBundle\Entity\Profile;
use Exception;

/**
 * load the Participants
 */
class LoadParticipants extends Fixture implements OrderedFixtureInterface
{
    /**
     * Constant to declare load order of fixture
     */
    private const ORDER_NUMBER = 8;

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
     * @inheritDoc
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $this->objectManager = $manager;
        $this->loadReferences();

        foreach ($this->meals as $meal) {
            $users = $this->getRandomUsers();

            foreach ($users as $user) {
                $participant = new Participant();
                $participant->setMeal($meal);
                $participant->setProfile($user);
                $participant->setCostAbsorbed(false);

                if ($participant->getMeal()->getDay()->getLockParticipationDateTime() < new DateTime) {
                    $participant->setOfferedAt(time());
                } else {
                    $participant->setOfferedAt(0);
                }

                $this->objectManager->persist($participant);
            }
        }
        $this->objectManager->flush();
    }

    /**
     * get the Order of Fixtures Loading
     */
    public function getOrder(): int
    {
        // load as eight
        return self::ORDER_NUMBER;
    }

    /**
     * load References
     */
    protected function loadReferences(): void
    {
        foreach ($this->referenceRepository->getReferences() as $referenceName => $reference) {
            if ($reference instanceof Meal) {
                // we can't just use $reference here, because
                // getReference() does some doctrine magic that getReferences() does not
                $this->meals[] = $this->getReference($referenceName);
            } elseif ($reference instanceof Profile) {
                $this->profiles[] = $this->getReference($referenceName);
            }
        }
    }

    /**
     * @return Profile[]
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
        } elseif ($number === 1) {
            $users[] = $this->profiles[array_rand($this->profiles)];
        }

        return $users;
    }
}
