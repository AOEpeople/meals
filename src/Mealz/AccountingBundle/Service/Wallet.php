<?php

namespace Mealz\AccountingBundle\Service;

use Mealz\AccountingBundle\Entity\TransactionRepository;
use Mealz\MealBundle\Entity\ParticipantRepository;
use Mealz\UserBundle\Entity\Profile;

class Wallet
{
    /**
     * @var participantRepo
     */
    protected $participantRepo;

    /**
     * @var transactionRepo
     */
    protected $transactionRepo;

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
