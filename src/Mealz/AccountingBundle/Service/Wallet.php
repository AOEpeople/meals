<?php

namespace App\Mealz\AccountingBundle\Service;

use App\Mealz\AccountingBundle\Entity\TransactionRepository;
use App\Mealz\MealBundle\Entity\ParticipantRepository;
use App\Mealz\UserBundle\Entity\Profile;

class Wallet
{
    private ParticipantRepository $participantRepo;

    private TransactionRepository $transactionRepo;

    public function __construct(ParticipantRepository $participantRepo, TransactionRepository $transactionRepo)
    {
        $this->participantRepo = $participantRepo;
        $this->transactionRepo = $transactionRepo;
    }

    /**
     * @param Profile $profile
     * @return float
     */
    public function getBalance(Profile $profile)
    {
        $username = $profile->getUsername();
        $costs = $this->participantRepo->getTotalCost($username);
        $transactions = $this->transactionRepo->getTotalAmount($username);

        return bcsub($transactions, $costs, 2);
    }
}
