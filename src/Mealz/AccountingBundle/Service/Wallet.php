<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Service;

use App\Mealz\AccountingBundle\Repository\TransactionRepositoryInterface;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\UserBundle\Entity\Profile;

class Wallet
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

    public function getBalance(Profile $profile): float
    {
        $username = $profile->getUsername();
        $costs = $this->participantRepo->getTotalCost($username);
        $transactions = $this->transactionRepo->getTotalAmount($username);

        return round($transactions - $costs, 2);
    }
}
