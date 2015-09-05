<?php

namespace Mealz\AccountingBundle\Controller;

use Doctrine\ORM\Query;
use Mealz\AccountingBundle\ParticipantList\ParticipantListFactory;
use Mealz\MealBundle\Controller\BaseController;

class AccountingController extends BaseController {

	public function listAction() {
		/** @var ParticipantListFactory $participantListFactory */
		$participantListFactory = $this->get('mealz_accounting.participant_list_factory');

		$startDay = new \DateTime('first day of last month');
		$endDay = new \DateTime('last day of last month');

		$participantList = $participantListFactory->getList($startDay, $endDay);

		return $this->render('MealzAccountingBundle:Accounting:list.html.twig', array(
			'startDay' => $startDay,
			'endDay' => $endDay,
			'participantList' => $participantList
		));
	}

}