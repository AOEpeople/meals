<?php

namespace App\Mealz\MealBundle\Service;

use App\Mealz\AccountingBundle\Repository\TransactionRepositoryInterface;
use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Repository\DayRepositoryInterface;
use App\Mealz\MealBundle\Repository\MealRepositoryInterface;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;

class ApiService
{
    private ParticipantRepositoryInterface $participantRepo;
    private TransactionRepositoryInterface $transactionRepo;
    private MealRepositoryInterface $mealRepo;
    private DayRepositoryInterface $dayRepo;
    private EventParticipationService $eventPartSrv;

    public function __construct(
        ParticipantRepositoryInterface $participantRepo,
        TransactionRepositoryInterface $transactionRepo,
        MealRepositoryInterface $mealRepo,
        DayRepositoryInterface $dayRepo,
        EventParticipationService $eventPartSrv
    ) {
        $this->participantRepo = $participantRepo;
        $this->transactionRepo = $transactionRepo;
        $this->mealRepo = $mealRepo;
        $this->dayRepo = $dayRepo;
        $this->eventPartSrv = $eventPartSrv;
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

    /**
     * @return Meal[]
     */
    public function findAllOn(DateTime $date): array
    {
        return $this->mealRepo->findAllOn($date);
    }

    public function getDayByDate(DateTime $dateTime): ?Day
    {
        return $this->dayRepo->getDayByDate($dateTime);
    }

    /**
     * Checks wether the parameter at a given key exists and is valid.
     *
     * @param array  $parameters decoded json content
     * @param string $key        key for the parameter to be checked
     * @param string $type       the expected type of the parameter
     */
    public function isParamValid($parameters, $key, $type): bool
    {
        return isset($parameters[$key])
            && null !== $parameters[$key]
            && $type === gettype($parameters[$key]);
    }

    public function getEventParticipationData(Day $day, Profile $profile = null): ?array
    {
        return $this->eventPartSrv->getEventParticipationData($day, $profile);
    }

    public function getEventParticipationInfo(Day $day): ?array
    {
        if (null === $day->getEvent()) {
            return null;
        }

        return [
            'name' => $day->getEvent()->getEvent()->getTitle(),
            'participants' => $this->getEventParticipants($day)
        ];
    }

    private function getEventParticipants(Day $day): array
    {
        return $this->eventPartSrv->getParticipants($day);
    }
}
