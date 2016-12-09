<?php

namespace Mealz\MealBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mealz\MealBundle\Entity\Dish;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\Participant;
use Mealz\UserBundle\Entity\Profile;

/**
 * load the Participants
 * Class LoadParticipants
 * @package Mealz\MealBundle\DataFixtures\ORM
 */
class LoadParticipants extends AbstractFixture implements OrderedFixtureInterface
{

    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @var array
     */
    protected $meals = array();

    /**
     * @var array
     */
    protected $profiles = array();

    /**
     * load the Object
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $this->objectManager = $manager;
        $this->loadReferences();

        foreach ($this->meals as $meal) {
            /** @var $meal Meal */
            $users = $this->getRandomUsers();
            foreach ($users as $user) {
                /** @var $user Profile */
                $participant = new Participant();
                $participant->setMeal($meal);
                $participant->setProfile($user);
                $participant->setCostAbsorbed(false);

                $this->objectManager->persist($participant);
            }
        }
        $this->objectManager->flush();
    }

    /**
     * get the Order of Fixtures Loading
     * @return mixed
     */
    public function getOrder()
    {
        /**
         * load as eigth
         */
        return 8;
    }

    /**
     * load References
     */
    protected function loadReferences()
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
     * @return array<Users>
     */
    protected function getRandomUsers()
    {
        $number = rand(0, count($this->profiles));
        $users = array();

        if ($number > 1) {
            foreach (array_rand($this->profiles, $number) as $userKey) {
                $users[] = $this->profiles[$userKey];
            }
        } elseif ($number == 1) {
            $users[] = $this->profiles[array_rand($this->profiles)];
        }

        return $users;
    }

}