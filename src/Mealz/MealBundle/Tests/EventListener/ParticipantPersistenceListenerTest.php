<?php

namespace Mealz\MealBundle\Tests\EventListener;

use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\Tests\AbstractRepositoryTestCase;

class ParticipantPersistenceListenerTest extends AbstractRepositoryTestCase {

	public function setUp() {
		parent::setUp();

		$this->clearAllTables();
	}

	public function testTriggersOnInsert() {
		$em = $this->getDoctrine()->getManager();

		// load test data
		$meal = $this->createMeal();
		$user = $this->createUser();
		$participant = new Participant();
		$participant->setUser($user);
		$participant->setMeal($meal);
		$participant2 = clone $participant;
		$this->persistAndFlushAll(array($meal, $meal->getDish(), $user, $participant));


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
		$user = $this->createUser();
		$participant = new Participant();
		$participant->setUser($user);
		$participant->setMeal($meal);
		$participant2 = new Participant();
		$participant2->setUser($user);
		$participant2->setMeal($meal2);
		$this->persistAndFlushAll(array($meal, $meal->getDish(), $meal2, $meal2->getDish(), $user, $participant, $participant2));

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
		$user = $this->createUser();
		$this->persistAndFlushAll(array($meal, $meal->getDish(), $user));


		$this->setExpectedException('RuntimeException');

		$participant = new Participant();
		$participant->setUser($user);
		$participant->setMeal($meal);

		$em->persist($participant);
		$em->flush();
	}

	public function testGuest() {
		$em = $this->getDoctrine()->getManager();

		// load test data
		$meal = $this->createMeal();
		$user = $this->createUser();
		$participant = new Participant();
		$participant->setUser($user);
		$participant->setMeal($meal);

		$this->persistAndFlushAll(array($meal, $meal->getDish(), $user, $participant));

		// now add a guest
		$participant2 = clone $participant;
		$participant2->setGuestName('john');

		$em->transactional(function($em) use($participant2) {
			$em->persist($participant2);
			$em->flush();
		});

		$this->addToAssertionCount(1); // no exception thrown

		$this->setExpectedException(
			'Mealz\\MealBundle\\EventListener\\ParticipantNotUniqueException'
		);

		$participant3 = clone $participant2;
		$em->transactional(function($em) use($participant3) {
			$em->persist($participant3);
			$em->flush();
		});
	}


}
