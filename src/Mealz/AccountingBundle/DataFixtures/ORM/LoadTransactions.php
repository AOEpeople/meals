<?php

namespace Mealz\AccountingBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Mealz\AccountingBundle\Entity\Transaction;
use Mealz\UserBundle\Entity\Profile;

/**
 * Class LoadTransactions
 * @package Mealz\AccountingBundle\DataFixtures\ORM
 */
class LoadTransactions extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Constant to declare load order of fixture
     */
    const ORDER_NUMBER = 4;
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * @param ObjectManager $manager
     */
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

    /**
     * @return int
     */
    public function getOrder()
    {
        return self::ORDER_NUMBER;
    }

    /**
     * @return array
     */
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
    /**
     * @param $user
     * @param $amount
     */
    private function addLastMonthTransaction($user, $amount)
    {
        // Generate some random date from last month
        $lastMonthTimestamp = strtotime('first day of previous month') + (mt_rand(1, 27) * 86400);
        $this->addTransaction($user, $amount, new \DateTime('@'.$lastMonthTimestamp));
    }

    /**
     * @param Profile   $user
     * @param float     $amount
     * @param \DateTime $date
     */
    private function addTransaction($user, $amount, \DateTime $date = null)
    {
        if (is_null($date) === true) {
            $date = new \DateTime();
        }
        // make transactions more realistic (random minute, NO identical Date)
        $date->modify('+' . mt_rand(1, 1400) . ' second');

        $transaction = new Transaction();
        $transaction->setDate($date);
        $transaction->setAmount($amount);
        $transaction->setProfile($user);
        $this->objectManager->persist($transaction);
    }
}
