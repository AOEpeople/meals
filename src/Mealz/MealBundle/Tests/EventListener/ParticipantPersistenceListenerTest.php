<?php

namespace Mealz\MealBundle\Tests\EventListener;

use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\EventListener\ParticipantNotUniqueException;
use Mealz\MealBundle\Tests\AbstractDatabaseTestCase;

class ParticipantPersistenceListenerTest extends AbstractDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
    }

    public function testTriggersOnInsert()
    {
        $enityManager = $this->getDoctrine()->getManager();

        // load test data
        $meal = $this->createMeal();
        $profile = $this->createProfile();
        $participant = new Participant();
        $participant->setProfile($profile);
        $participant->setMeal($meal);
        $participant2 = clone $participant;
        $this->persistAndFlushAll(array($meal, $meal->getDish(), $profile, $participant));


        // persist second participant
        $this->expectException(ParticipantNotUniqueException::class);

        $enityManager->transactional(function ($enityManager) use ($participant2) {
            $enityManager->persist($participant2);
            $enityManager->flush();
        });
    }

    public function testTriggersOnUpdate()
    {
        $enityManager = $this->getDoctrine()->getManager();

        // load test data
        $meal = $this->createMeal();
        $meal2 = $this->createMeal();
        $profile = $this->createProfile();
        $participant = new Participant();
        $participant->setProfile($profile);
        $participant->setMeal($meal);
        $participant2 = new Participant();
        $participant2->setProfile($profile);
        $participant2->setMeal($meal2);
        $this->persistAndFlushAll(array($meal, $meal->getDish(), $meal2, $meal2->getDish(), $profile, $participant, $participant2));

        // change first participant
        $this->expectException(ParticipantNotUniqueException::class);

        $participant2->setMeal($meal);

        $enityManager->transactional(function ($enityManager) use ($participant2) {
            $enityManager->persist($participant2);
            $enityManager->flush();
        });
    }

    /**
     * the transaction is needed because a SELECT query is used in order to find
     * an already existing participant
     */
    public function testThrowsExceptionWhenNotUsedInATransaction()
    {
        $enityManager = $this->getDoctrine()->getManager();

        // load test data
        $meal = $this->createMeal();
        $profile = $this->createProfile();
        $this->persistAndFlushAll(array($meal, $meal->getDish(), $profile));

        $this->expectException(\RuntimeException::class);

        $participant = new Participant();
        $participant->setProfile($profile);
        $participant->setMeal($meal);

        $enityManager->persist($participant);
        $enityManager->flush();
    }
}
