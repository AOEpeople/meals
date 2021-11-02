<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\EventListener;

use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\EventListener\ParticipantNotUniqueException;
use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use Doctrine\ORM\EntityManager;
use RuntimeException;

class ParticipantPersistenceListenerTest extends AbstractDatabaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
    }

    public function testTriggersOnInsert(): void
    {
        // load test data
        $meal = $this->createMeal();
        $profile = $this->createProfile();
        $participant1 = new Participant($profile, $meal);
        $participant2 = clone $participant1;
        $this->persistAndFlushAll([$meal, $meal->getDish(), $profile, $participant1]);

        // persist second participant
        $this->expectException(ParticipantNotUniqueException::class);

        /** @var EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->transactional(static function ($entityManager) use ($participant2) {
            $entityManager->persist($participant2);
            $entityManager->flush();
        });
    }

    public function testTriggersOnUpdate(): void
    {
        // load test data
        $meal1 = $this->createMeal();
        $meal2 = $this->createMeal();
        $profile = $this->createProfile();
        $participant1 = new Participant($profile, $meal1);
        $participant2 = new Participant($profile, $meal2);
        $this->persistAndFlushAll([$meal1, $meal1->getDish(), $meal2, $meal2->getDish(), $profile, $participant1, $participant2]);

        // change first participant
        $this->expectException(ParticipantNotUniqueException::class);

        $participant2->setMeal($meal1);

        /** @var EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->transactional(static function ($entityManager) use ($participant2) {
            $entityManager->persist($participant2);
            $entityManager->flush();
        });
    }

    /**
     * the transaction is needed because a SELECT query is used in order to find
     * an already existing participant
     */
    public function testThrowsExceptionWhenNotUsedInATransaction(): void
    {
        // load test data
        $meal = $this->createMeal();
        $profile = $this->createProfile();
        $this->persistAndFlushAll([$meal, $meal->getDish(), $profile]);

        $this->expectException(RuntimeException::class);

        $participant = new Participant($profile, $meal);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($participant);
        $entityManager->flush();
    }
}
