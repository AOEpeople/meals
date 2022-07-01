<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Service;

use App\Mealz\AccountingBundle\Entity\TransactionRepository;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;

class Wallet
{
    private ParticipantRepositoryInterface $participantRepo;

    private TransactionRepository $transactionRepo;

    public function __construct(ParticipantRepositoryInterface $participantRepo, TransactionRepository $transactionRepo)
    {
        $this->participantRepo = $participantRepo;
        $this->transactionRepo = $transactionRepo;
    }

    /**
     * @throws NonUniqueResultException
     * @throws NoResultException
     */
    public function getBalance(Profile $profile): float
    {
        $username = $profile->getUsername();
        $costs = $this->participantRepo->getTotalCost($username);
        $transactions = $this->transactionRepo->getTotalAmount($username);

        return $transactions - $costs;
    }
}
