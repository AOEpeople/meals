<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\EventListener;

use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\EventListener\ParticipantNotUniqueException;
use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;
use Doctrine\ORM\EntityManager;
use Override;

final class ParticipantPersistenceListenerTest extends AbstractDatabaseTestCase
{
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
    }

    public function testTriggersOnInsert(): void
    {
        // load test data
        $meal = $this->createMeal();
        $profile1 = $this->createProfile();
        $participant1 = new Participant($profile1, $meal);
        $profile2 = $this->createProfile();
        $participant2 = new Participant($profile2, $meal);
        $this->persistAndFlushAll([$meal, $profile1, $participant1]);

        // persist second participant
        $this->expectException(ParticipantNotUniqueException::class);

        /** @var EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->wrapInTransaction(static function ($entityManager) use ($participant2) {
            $entityManager->persist($participant2);
            $entityManager->flush();
        });
    }

    public function testTriggersOnUpdate(): void
    {
        // load test data
        $meal1 = $this->createMeal();
        $meal2 = $this->createMeal();
        $profile1 = $this->createProfile();
        $profile2 = $this->createProfile();
        $participant1 = new Participant($profile1, $meal1);
        $participant2 = new Participant($profile2, $meal2);
        $this->persistAndFlushAll([$meal1, $meal1->getDish(), $meal1->getDay(), $meal2, $meal2->getDish(), $meal2->getDay(), $profile1, $profile2, $participant1, $participant2]);

        // change first participant
        $this->expectException(ParticipantNotUniqueException::class);

        $participant2->setMeal($meal1);

        /** @var EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->wrapInTransaction(static function ($entityManager) use ($participant2) {
            $entityManager->persist($participant2);
            $entityManager->flush();
        });
    }
}
