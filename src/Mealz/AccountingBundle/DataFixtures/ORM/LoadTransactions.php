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
                $this->addTransaction($user);
            }
        }

        $this->objectManager->flush();
    }

    /**
     * @param $user
     */
    private function addTransaction($user)
    {
        $transaction = new Transaction();
        $transaction->setAmount(mt_rand(1000, 5000)/100);
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
