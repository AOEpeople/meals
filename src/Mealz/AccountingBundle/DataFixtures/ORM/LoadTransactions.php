<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\DataFixtures\ORM;

use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Persistence\ObjectManager;
use Exception;

class LoadTransactions extends Fixture implements OrderedFixtureInterface
{
    private const USER_WITH_TRANSACTION = 'alice.meals';
    private const USER_WITHOUT_TRANSACTION = 'john.meals';

    /**
     * Constant to declare load order of fixture.
     */
    private const ORDER_NUMBER = 4;

    protected ObjectManager $objectManager;

    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $this->objectManager = $manager;

        for ($i = 0; $i < 3; ++$i) {
            $randomUsers = $this->getRandomUsers();
            foreach ($randomUsers as $user) {
                if (self::USER_WITHOUT_TRANSACTION !== $user->getUsername()) {
                    $this->addTransaction($user, random_int(500, 1200) / 100);
                    $this->addLastMonthTransaction($user, random_int(500, 1000) / 100);
                }
            }
        }

        foreach ($this->referenceRepository->getReferences() as $reference) {
            if ($reference instanceof Profile && self::USER_WITH_TRANSACTION === $reference->getUsername()) {
                $this->addTransaction($reference, random_int(500, 1200) / 100);
                $this->addLastMonthTransaction($reference, random_int(500, 1000) / 100);
            }
        }

        $this->objectManager->flush();
    }

    public function getOrder(): int
    {
        return self::ORDER_NUMBER;
    }

    /**
     * @return Profile[]
     *
     * @throws Exception
     */
    protected function getRandomUsers(): array
    {
        $profiles = [];
        foreach ($this->referenceRepository->getReferences() as $referenceName => $reference) {
            if ($reference instanceof Profile) {
                $profiles[] = $this->getReference($referenceName);
            }
        }

        $number = random_int(1, count($profiles));
        $profilesToReturn = [];

        if ($number === 1) {
            $profilesToReturn[] = $profiles[array_rand($profiles)];
        } else if ($number > 1) {
            foreach (array_rand($profiles, $number) as $userKey) {
                $profilesToReturn[] = $profiles[$userKey];
            }
        }

        return $profilesToReturn;
    }

    /**
     * @throws Exception
     */
    private function addLastMonthTransaction(Profile $user, float $amount): void
    {
        // Generate some random date from last month
        $lastMonthTimestamp = strtotime('first day of previous month') + (random_int(1, 27) * 86400);
        $this->addTransaction($user, $amount, new DateTime('@' . $lastMonthTimestamp));
    }

    /**
     * @throws Exception
     */
    private function addTransaction(Profile $user, float $amount, DateTime $date = null): void
    {
        if (true === is_null($date)) {
            $date = new DateTime();
        }

        // make transactions more realistic (random minute, NO identical Date)
        $date->modify('+' . random_int(1, 1400) . ' second');

        $transaction = new Transaction();
        $transaction->setDate($date);
        $transaction->setAmount($amount);
        $transaction->setProfile($user);
        $this->objectManager->persist($transaction);
    }
}
