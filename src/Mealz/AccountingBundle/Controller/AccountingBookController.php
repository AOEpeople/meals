<?php

namespace Mealz\AccountingBundle\Controller;

use Mealz\MealBundle\Controller\BaseController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class AccountingBookController
 * @package Mealz\AccountingBundle\Controller
 */
class AccountingBookController extends BaseController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        // Deny access for unprivileged (non-admin) users
        if (!$this->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

		// Get first and last day of previous month
		$minDateFirst = new \DateTime('first day of previous month');
		$minDateFirst->setTime(0, 0, 0);
		$maxDateFirst = new \DateTime('last day of previous month');
		$maxDateFirst->setTime(23, 59, 59);

		// Get first and last day of actual month
		$minDate = new \DateTime('first day of this month');
		$minDate->setTime(0, 0, 0);
		$maxDate = new \DateTime('today');
		$maxDate->setTime(23, 59, 59);

		// Create headline for twig template
		$headingFirst = $minDateFirst->format('d.m.-') . $maxDateFirst->format('d.m.Y');
		$heading = $minDate->format('d.m.-') . $maxDate->format('d.m.Y');

		// Get array of users with their amount of transactions in previous month
		$transactionRepository = $this->getTransactionRepository();
		$usersFirst = $transactionRepository->findUserDataAndTransactionAmountForGivenPeriod($minDateFirst, $maxDateFirst);

		// Get array of users with their amount of transactions in actual month
		$users = $transactionRepository->findUserDataAndTransactionAmountForGivenPeriod($minDate, $maxDate);

		return $this->render('MealzAccountingBundle:Accounting\\Admin:accountingBook.html.twig', array(
			'headingFirst' => $headingFirst,
			'heading' => $heading,
			'usersFirst' => $usersFirst,
			'users' => $users
		));
	}
}