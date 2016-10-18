<?php

namespace Mealz\AccountingBundle\Entity;

use Mealz\UserBundle\Entity\Profile;

/**
 * TransactionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class TransactionRepository extends \Doctrine\ORM\EntityRepository
{
    const COLUMN_NAME = 'amount';

    /**
     * Get total amount of transactions. Prevent unnecessary ORM mapping.
     *
     * @param string $username
     * @return float
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getTotalAmount($username)
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('SUM(t.amount) AS amount');
        $qb->andWhere('t.profile = :user');
        $qb->setParameter('user', $username);

        return floatval($qb->getQuery()->getSingleScalarResult());
    }

    /**
     * @param Profile $profile
     * @return Transaction[]
     */
    public function getLastSuccessfulTransactions(Profile $profile, $limit = NULL) {
        $qb = $this->createQueryBuilder('t');
        $qb->select('t');
        $qb->andWhere('t.user = :user');
        $qb->setParameter('user', $profile);

        $qb->orderBy('t.date', 'desc');
        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->execute();
    }


    /**
     * @param \DateTime $minDate
     * @param \DateTime $maxDate
     * @param Profile $profile
     * @return Transaction[]
     */
    public function getSuccessfulTransactionsOnDays(\DateTime $minDate, \DateTime $maxDate, Profile $profile) {
        $qb = $this->createQueryBuilder('t');
        $qb->select('t');

        $minDate = clone $minDate;
        $minDate->setTime(0, 0, 0);
        $maxDate = clone $maxDate;
        $maxDate->setTime(23, 59, 59);

        $qb->andWhere('t.date >= :minDate');
        $qb->andWhere('t.date <= :maxDate');
        $qb->setParameter('minDate', $minDate);
        $qb->setParameter('maxDate', $maxDate);

        $qb->andWhere('t.user = :user');
        $qb->setParameter('user', $profile);

        $qb->orderBy('t.date', 'DESC');

        return $qb->getQuery()->execute();
    }

    /**
     * Get first name, last name and amount of transactions in the given time per user.
     *
     * @param \DateTime $minDate
     * @param \DateTime $maxDate
     * @return array
     */
    public function findTotalAmountOfTransactionsPerUser(\DateTime $minDate = null, \DateTime $maxDate = null)
    {
        $qb = $this->createQueryBuilder('t');
        $qb->select('p.username, p.firstName, p.name, SUM(t.amount) AS amount');
        $qb->leftJoin('t.profile', 'p');

        if ($minDate) {
            $qb->andWhere('t.date >= :minDate');
            $qb->setParameter('minDate', $minDate);
        }

        if ($maxDate) {
            $qb->andWhere('t.date <= :maxDate');
            $qb->setParameter('maxDate', $maxDate);
        }

        $qb->groupBy('p.username');
        $qb->orderBy('p.name, p.firstName');
        $queryResult = $qb->getQuery()->getArrayResult();

        $result = array();

        foreach($queryResult as $item) {
            $result[$item['username']] = array(
                'firstName' => $item['firstName'],
                'name' => $item['name'],
                'amount' => $item['amount'],
            );
        }

        return $result;
    }
}
