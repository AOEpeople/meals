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
use RuntimeException;

class LoadTransactions extends Fixture implements OrderedFixtureInterface
{
    /**
     * User guaranteed to have a transaction.
     */
    private const USER_WITH_TRANSACTION = 'alice.meals';

    /**
     * User guaranteed to have no transaction.
     */
    private const USER_WITHOUT_TRANSACTION = 'john.meals';

    /**
     * Constant to declare load order of fixture.
     */
    private const ORDER_NUMBER = 4;

    private ObjectManager $objectManager;

    /**
     * @var Profile[]
     */
    private ?array $profiles = null;

    /**
     * @throws Exception
     */
    public function load(ObjectManager $manager): void
    {
        $this->objectManager = $manager;
        $usersWithTransactions = [];

        for ($i = 0; $i < 5; ++$i) {
            foreach ($this->getRandomUsers() as $user) {
                if (self::USER_WITHOUT_TRANSACTION === $user->getUsername()) {
                    continue;
                }

                $date = new DateTime('first day of -' . $i . 'months');
                $this->addTransaction($user, random_int(20000, 40000) / 100, $date);
                $usersWithTransactions[$user->getUsername()] = '';
            }
        }

        // ensure one transaction for USER_WITH_TRANSACTION
        if (!isset($usersWithTransactions[self::USER_WITH_TRANSACTION])) {
            $user = $this->getUser(self::USER_WITH_TRANSACTION);
            $this->addTransaction($user, 200.00, new DateTime());
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
        $profilesToReturn = [];
        $profiles = $this->getProfiles();
        $number = random_int(2, count($profiles));

        foreach (array_rand($profiles, $number) as $userKey) {
            $profilesToReturn[$userKey] = $profiles[$userKey];
        }

        return array_values($profilesToReturn);
    }

    private function getProfiles(): array
    {
        if (null === $this->profiles) {
            $this->profiles = [];
            foreach ($this->referenceRepository->getReferences() as $key => $reference) {
                if ($reference instanceof Profile) {
                    $this->profiles[$reference->getUsername()] = $this->getReference($key);
                }
            }
        }

        return $this->profiles;
    }

    private function getUser(string $username): Profile
    {
        $profiles = $this->getProfiles();

        if (!isset($profiles[$username])) {
            throw new RuntimeException($username . ': user not found');
        }

        return $profiles[$username];
    }

    /**
     * @throws Exception
     */
    private function addTransaction(Profile $user, float $amount, DateTime $date = null): void
    {
        $transaction = new Transaction();
        $transaction->setDate($date ?? new DateTime());
        $transaction->setAmount($amount);
        $transaction->setProfile($user);

        $this->objectManager->persist($transaction);
    }
}
