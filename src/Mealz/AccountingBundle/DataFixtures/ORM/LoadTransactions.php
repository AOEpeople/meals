<?php

namespace Mealz\AccountingBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mealz\AccountingBundle\Entity\Transaction;
use Mealz\UserBundle\Entity\Profile;

class LoadTransactions extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    public function load(ObjectManager $manager)
    {
        $this->objectManager = $manager;
        $randomUsers = $this->getRandomUsers();
        for ($i = 0; $i < 6; $i++) {
            foreach ($randomUsers as $user) {
                $this->addTransaction($user, mt_rand(1000, 5000)/100);
                $this->addLastMonthTransaction($user, mt_rand(1000, 5000)/100);
            }
        }

        $this->objectManager->flush();
    }

    private function addLastMonthTransaction($user, $amount)
    {
        // Generate some random date from last month
        $lastMonthTimestamp = strtotime('first day of previous month') + (mt_rand(1, 27) * 86400);
        $this->addTransaction($user, $amount, new \DateTime('@' . $lastMonthTimestamp));
    }

    /**
     * @param Profile   $user
     * @param float     $amount
     * @param \DateTime $date
     */
    private function addTransaction($user, $amount, \DateTime $date = NULL)
    {
        if (is_null($date)) {
            $date = new \DateTime();
        }

        $transaction = new Transaction();
        $transaction->setDate($date);
        $transaction->setAmount($amount);
        $transaction->setProfile($user);
        $this->objectManager->persist($transaction);
    }

    private function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    protected function getRandomUsers()
    {
        $profiles = array();
        foreach ($this->referenceRepository->getReferences() as $referenceName => $reference) {
            if ($reference instanceof Profile) {
                $profiles[] = $this->getReference($referenceName);
            }
        }
        return $profiles;
    }

    public function getOrder()
    {
        return 4;
    }
}
