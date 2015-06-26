<?php

namespace Mealz\MealBundle\Controller;

use Doctrine\ORM\Query;
use Mealz\MealBundle\Entity\DishRepository;
use Mealz\MealBundle\Entity\Meal;
use Mealz\MealBundle\Entity\Participant;

class AccountingController extends BaseController {

	public function listAction() {
        $startTime = new \DateTime();
        $startTime->setTime(0,0,0);
        $endTime = clone $startTime;
        $endTime->modify('+1 day -1 second');

        $participants = $this->getParticipantRepository()->getParticipants($startTime, $endTime);

		return $this->render('MealzMealBundle:Accounting:list.html.twig', array(
			'participants' => $participants
		));
	}

}