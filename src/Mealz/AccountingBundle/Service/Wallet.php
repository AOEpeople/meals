<?php

namespace Mealz\AccountingBundle\Service;

use Mealz\AccountingBundle\ParticipantList\ParticipantListFactory;
use Mealz\AccountingBundle\Entity\TransactionRepository;
use Mealz\UserBundle\Entity\Profile;

class Wallet
{
    protected $participantList;

    protected $transactionRepository;

    public function __construct(ParticipantListFactory $participantListFactory, TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
        $this->participantList = $participantListFactory->getList(new \DateTime('2015-01-01'), new \DateTime());
    }

    /**
     * @param Profile $profile
     * @return int
     */
    public function getBalance(Profile $profile)
    {
        $costs = $this->participantList->countAccountableParticipations($profile);
        $transactions = $this->transactionRepository->findByUser($profile->getName());

        return $this->getTransactionsAmount($transactions) - $costs;
    }

    /**
     * @param array $transactions
     * @return int
     */
    private function getTransactionsAmount($transactions)
    {
        $paymentValue = 0;
        foreach ($transactions as $transaction) {
            $paymentValue += $transaction->getAmount();
        }
        return $paymentValue;
    }
}