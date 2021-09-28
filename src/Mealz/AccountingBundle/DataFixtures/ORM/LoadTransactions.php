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
    /**
     * Constant to declare load order of fixture
     */
    private const ORDER_NUMBER = 4;

    protected ObjectManager $objectManager;

    /**
     * @param ObjectManager $manager
     *
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $this->objectManager = $manager;

        for ($i = 0; $i < 3; $i++) {
            $randomUsers = $this->getRandomUsers();
            foreach ($randomUsers as $user) {
                $this->addTransaction($user, random_int(500, 1200)/100);
                $this->addLastMonthTransaction($user, random_int(500, 1000)/100);
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

        $number = random_int(0, count($profiles));
        $users = [];

        if ($number > 1) {
            foreach (array_rand($profiles, $number) as $userKey) {
                $users[] = $profiles[$userKey];
            }
        } elseif ($number === 1) {
            $users[] = $profiles[array_rand($profiles)];
        }

        return $users;
    }

    /**
     * @throws Exception
     */
    private function addLastMonthTransaction(Profile $user, float $amount): void
    {
        // Generate some random date from last month
        $lastMonthTimestamp = strtotime('first day of previous month') + (random_int(1, 27) * 86400);
        $this->addTransaction($user, $amount, new DateTime('@'.$lastMonthTimestamp));
    }

    /**
     * @throws Exception
     */
    private function addTransaction(Profile $user, float $amount, DateTime $date = null): void
    {
        if (is_null($date) === true) {
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
