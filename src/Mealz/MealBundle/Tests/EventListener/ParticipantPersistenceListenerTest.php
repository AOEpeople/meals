<?php

namespace Mealz\MealBundle\Tests\EventListener;

use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\Tests\AbstractDatabaseTestCase;

class ParticipantPersistenceListenerTest extends AbstractDatabaseTestCase {

	public function setUp() {
		parent::setUp();

		$this->clearAllTables();
	}

	public function testTriggersOnInsert() {
		$em = $this->getDoctrine()->getManager();

		// load test data
		$meal = $this->createMeal();
		$profile = $this->createProfile();
		$participant = new Participant();
		$participant->setProfile($profile);
		$participant->setMeal($meal);
		$participant2 = clone $participant;
		$this->persistAndFlushAll(array($meal, $meal->getDish(), $profile, $participant));


		// persist second participant
		$this->setExpectedException('Mealz\\MealBundle\\EventListener\\ParticipantNotUniqueException');

		$em->transactional(function($em) use($participant2) {
			$em->persist($participant2);
			$em->flush();
		});

	}

	public function testTriggersOnUpdate() {
		$em = $this->getDoctrine()->getManager();

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
		$this->setExpectedException('Mealz\\MealBundle\\EventListener\\ParticipantNotUniqueException');

		$participant2->setMeal($meal);

		$em->transactional(function($em) use($participant2) {
			$em->persist($participant2);
			$em->flush();
		});
	}

	/**
	 * the transaction is needed because a SELECT query is used in order to find
	 * an already existing participant
	 */
	public function testThrowsExceptionWhenNotUsedInATransaction() {
		$em = $this->getDoctrine()->getManager();

		// load test data
		$meal = $this->createMeal();
		$profile = $this->createProfile();
		$this->persistAndFlushAll(array($meal, $meal->getDish(), $profile));


		$this->setExpectedException('RuntimeException');

		$participant = new Participant();
		$participant->setProfile($profile);
		$participant->setMeal($meal);

		$em->persist($participant);
		$em->flush();
	}
}
