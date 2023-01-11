<?php

namespace App\Mealz\MealBundle\Service;

use App\Mealz\AccountingBundle\Repository\TransactionRepositoryInterface;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;

class ApiService
{
    private ParticipantRepositoryInterface $participantRepo;
    private TransactionRepositoryInterface $transactionRepo;

    public function __construct(
        ParticipantRepositoryInterface $participantRepo,
        TransactionRepositoryInterface $transactionRepo
    ) {
        $this->participantRepo = $participantRepo;
        $this->transactionRepo = $transactionRepo;
    }

    /**
     * Merge participation and transactions into 1 array.
     */
    public function getFullTransactionHistory(DateTime $dateFrom, DateTime $dateTo, Profile $profile): array
    {
        $participations = $this->participantRepo->getParticipantsOnDays($dateFrom, $dateTo, $profile);

        $transactions = $this->transactionRepo->getSuccessfulTransactionsOnDays($dateFrom, $dateTo, $profile);

        $costDifference = 0;
        $transactionHistory = [];
        foreach ($transactions as $transaction) {
            $costDifference += $transaction->getAmount();

            $timestamp = $transaction->getDate()->getTimestamp();
            $date = $transaction->getDate();
            $description = $transaction->getPaymethod();
            $amount = $transaction->getAmount();

            $credit = [
                'type' => 'credit',
                'timestamp' => $timestamp,
                'date' => $date,
                'description_en' => $description,
                'description_de' => $description,
                'amount' => $amount,
            ];

            $transactionHistory[] = $credit;
        }

        foreach ($participations as $participation) {
            $costDifference -= $participation->getMeal()->getPrice();
            $timestamp = $participation->getMeal()->getDateTime()->getTimestamp();
            $mealId = $participation->getMeal()->getId();

            $date = $participation->getMeal()->getDateTime();
            $description_en = $participation->getMeal()->getDish()->getTitleEn();
            $description_de = $participation->getMeal()->getDish()->getTitleDe();
            $amount = $participation->getMeal()->getPrice();

            $debit = [
                'type' => 'debit',
                'date' => $date,
                'timestamp' => $timestamp . '-' . $mealId,
                'description_en' => $description_en,
                'description_de' => $description_de,
                'amount' => $amount,
            ];

            $transactionHistory[] = $debit;
        }

        $costDifference = round($costDifference, 2);

        return [$costDifference, $transactionHistory];
    }
}
