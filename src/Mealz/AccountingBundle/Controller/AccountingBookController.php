<?php

namespace Mealz\AccountingBundle\Controller;

use Mealz\MealBundle\Controller\BaseController;

class AccountingBookController extends BaseController
{
    public function listAction()
    {
        $minDate = new \DateTime('first day of previous month');
        $minDate->setTime(0, 0, 0);
        $maxDate = new \DateTime('last day of previous month');
        $maxDate->setTime(23, 59, 59);

        $heading = $minDate->format('d.m.-') . $maxDate->format('d.m.Y');

        $transactionRepository = $this->getTransactionRepository();
        $users = $transactionRepository->findTotalAmountOfTransactionsPerUser($minDate, $maxDate);

        return $this->render('MealzAccountingBundle:Accounting\\Admin:accountingBook.html.twig', array(
            'heading' => $heading,
            'users' => $users
        ));
    }
}