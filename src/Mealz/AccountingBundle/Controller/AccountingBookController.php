<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Controller;

use App\Mealz\AccountingBundle\Repository\TransactionRepositoryInterface;
use App\Mealz\MealBundle\Controller\BaseController;
use DateTime;
use Exception;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_KITCHEN_STAFF')]
class AccountingBookController extends BaseController
{
    public function list(TransactionRepositoryInterface $transactionRepo): JsonResponse
    {
        // Get first and last day of previous month
        $minDateFirst = new DateTime('first day of previous month');
        $minDateFirst->setTime(0, 0);
        $maxDateFirst = new DateTime('last day of previous month');
        $maxDateFirst->setTime(23, 59, 59);

        // Get first and last day of actual month
        $minDate = new DateTime('first day of this month');
        $minDate->setTime(0, 0);
        $maxDate = new DateTime('today');
        $maxDate->setTime(23, 59, 59);

        // Create headline for twig template
        $headingFirst = $minDateFirst->format('d.m.-') . $maxDateFirst->format('d.m.Y');
        $heading = $minDate->format('d.m.-') . $maxDate->format('d.m.Y');

        // Get array of users with their amount of transactions in previous month
        $usersFirst = $transactionRepo->findUserDataAndTransactionAmountForGivenPeriod($minDateFirst, $maxDateFirst);

        // Get array of users with their amount of transactions in actual month
        $users = $transactionRepo->findUserDataAndTransactionAmountForGivenPeriod($minDate, $maxDate);

        return new JsonResponse([
            'lastMonth' => $headingFirst,
            'thisMonth' => $heading,
            'usersLastMonth' => $usersFirst,
            'usersThisMonth' => $users,
        ], Response::HTTP_OK);
    }

    /**
     * List all transactions that were payed by cash on the finances page.
     *
     * @throws Exception
     */
    public function listAllTransactions(?string $dateRange, TransactionRepositoryInterface $transactionRepo
    ): JsonResponse {
        $response = [];

        if (null === $dateRange) {
            // Get first and last day of previous month
            $minDateFirst = new DateTime('first day of previous month');
            $minDateFirst->setTime(0, 0);
            $maxDateFirst = new DateTime('last day of previous month');
            $maxDateFirst->setTime(23, 59, 59);

            // Create headline for twig template
            $headingFirst = $minDateFirst->format('d.m.') . ' - ' . $maxDateFirst->format('d.m.Y');

            // Get first and last day of actual month
            $minDate = new DateTime('first day of this month');
            $maxDate = new DateTime('today');
            $minDate->setTime(0, 0);
            $maxDate->setTime(23, 59, 59);

            $transactionsFirst = $transactionRepo->findAllTransactionsInDateRange($minDateFirst, $maxDateFirst);
            $response[] = [
                'heading' => $headingFirst,
                'transactions' => $transactionsFirst,
            ];
        } else {
            // Get date range set with date range picker by user
            $dateRangeArray = explode('&', $dateRange);
            $minDate = new DateTime($dateRangeArray[0]);
            $maxDate = new DateTime($dateRangeArray[1]);
        }

        $heading = $minDate->format('d.m.') . ' - ' . $maxDate->format('d.m.Y');

        $transactions = $transactionRepo->findAllTransactionsInDateRange($minDate, $maxDate);
        $response[] = [
            'heading' => $heading,
            'transactions' => $transactions,
        ];

        return new JsonResponse($response, Response::HTTP_OK);
    }
}
