<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Mealz\UserBundle\Entity\Profile;
use Mealz\UserBundle\Entity\Role;

/**
 * the Participant Repository
 * Class ParticipantRepository
 * @package Mealz\MealBundle\Entity
 */
class ParticipantRepository extends EntityRepository
{

    protected $defaultOptions = array(
        'load_meal' => false,
        'load_profile' => true,
    );

    /**
     * @param $options
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilderWithOptions($options)
    {
        $qb = $this->createQueryBuilder('p');

        // SELECT
        $select = 'p';
        if ($options['load_meal']) {
            $select .= ',m,d';
        }
        if ($options['load_profile']) {
            $select .= ',u';
        }
        $qb->select($select);

        // JOIN
        if ($options['load_meal']) {
            $qb->leftJoin('p.meal', 'm');
            $qb->leftJoin('m.dish', 'd');
        }
        if ($options['load_profile']) {
            $qb->leftJoin('p.profile', 'u');
        }

        return $qb;
    }

    public function getParticipantsOnDays(
        \DateTime $minDate,
        \DateTime $maxDate,
        Profile $profile = null,
        $options = array()
    ) {
        $options = array_merge(
            $options,
            array(
                'load_meal' => true,
                'load_profile' => true,
            )
        );
        if ($profile) {
            $options['load_profile'] = true;
        }
        $qb = $this->getQueryBuilderWithOptions($options);

        $minDate = clone $minDate;
        $minDate->setTime(0, 0, 0);
        $maxDate = clone $maxDate;
        $maxDate->setTime(23, 59, 59);

        $qb->andWhere('m.dateTime >= :minDate');
        $qb->andWhere('m.dateTime <= :maxDate');
        $qb->setParameter('minDate', $minDate);
        $qb->setParameter('maxDate', $maxDate);

        if ($profile) {
            $qb->andWhere('u.username = :username');
            $qb->setParameter('username', $profile->getUsername());
        }

        $qb->orderBy('u.name', 'ASC');

        $participants = $qb->getQuery()->execute();

        return $this->sortParticipantsByName($participants);
    }

    /**
     * helper function to sort participants by their name or guest name
     */
    public function sortParticipantsByName($participants)
    {
        usort($participants, array($this, 'compareNameOfParticipants'));

        return $participants;
    }

    protected function compareNameOfParticipants(Participant $participant1, Participant $participant2)
    {
        $result = strcasecmp($participant1->getProfile()->getName(), $participant2->getProfile()->getName());

        if ($result !== 0) {
            return $result;
        } elseif ($participant1->getMeal()->getDateTime() < $participant2->getMeal()->getDateTime()) {
            return 1;
        } elseif ($participant1->getMeal()->getDateTime() > $participant2->getMeal()->getDateTime()) {
            return -1;
        }

        return 0;
    }

    /**
     * Get total costs of participations. Prevent unnecessary ORM mapping.
     *
     * @param string $username
     * @return float
     * @throws \Doctrine\DBAL\DBALException
     */
    public function getTotalCost($username)
    {
        $qb = $this->getQueryBuilderWithOptions(
            [
                'load_meal' => true,
                'load_profile' => false,
            ]
        );

        $qb->select('SUM(m.price) as blubber');
        $qb->leftJoin('m.day', 'day');
        $qb->leftJoin('day.week', 'w');
        $qb->andWhere('p.profile = :user');
        $qb->setParameter('user', $username);
        $qb->andWhere('p.costAbsorbed = 0');
        $qb->andWhere('day.enabled = 1');
        $qb->andWhere('w.enabled = 1');
        $qb->andWhere('m.dateTime <= :now');
        $qb->setParameter('now', new \DateTime());

        return floatval($qb->getQuery()->getSingleScalarResult());
    }

    /**
     * @param Profile $profile
     * @param int $limit
     * @return Participant[]
     */
    public function getLastAccountableParticipations(Profile $profile, $limit = null)
    {
        $qb = $this->getQueryBuilderWithOptions(
            [
                'load_meal' => true,
                'load_profile' => false,
            ]
        );

        $qb->andWhere('p.profile = :user');
        $qb->setParameter('user', $profile);
        $qb->andWhere('p.costAbsorbed = :costAbsorbed');
        $qb->setParameter('costAbsorbed', false);
        $qb->andWhere('m.dateTime <= :now');
        $qb->setParameter('now', new \DateTime());

        $qb->orderBy('m.dateTime', 'desc');
        if ($limit) {
            $qb->setMaxResults($limit);
        }

        return $qb->getQuery()->execute();
    }

    public function findCostsGroupedByUserGroupedByMonth()
    {
        $costs = $this->findCostsPerMonthPerUser();

        $result = array();

        foreach ($costs as $cost) {
            $username = $cost['username'];
            $timestamp = strtotime($cost['yearMonth']);
            $costByMonth = array(
                'timestamp' => $timestamp,
                'costs' => $cost['costs'],
            );
            if (isset($result[$username])) {
                $result[$username]['costs'][] = $costByMonth;
            } else {
                $result[$username] = array(
                    'name' => $cost['name'],
                    'firstName' => $cost['firstName'],
                    'costs' => array($costByMonth),
                );
            }
        }

        return $result;

    }

    /**
     * Gets the aggregated monthly cost of all the participants.
     *
     * Guests are currently excluded from the result.
     *
     * @return array
     */
    private function findCostsPerMonthPerUser()
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('u.username, u.name, u.firstName, SUBSTRING(m.dateTime, 1, 7) AS yearMonth, SUM(m.price) AS costs');
        $qb->leftJoin('p.meal', 'm');
        $qb->leftJoin('p.profile', 'u');
        $qb->leftJoin('u.roles', 'r');
        $qb->leftJoin('m.day', 'd');
        $qb->leftJoin('d.week', 'w');
        /**
         * @TODO: optimize query. where clause costs a lot of time.
         */
        $qb->where('p.costAbsorbed = 0');
        $qb->andWhere(
            $qb->expr()->orX(
                $qb->expr()->isNull('r.sid'),
                $qb->expr()->neq('r.sid', ':role_sid')
            )
        );
        $qb->andWhere('m.dateTime < :now');
        $qb->andWhere('d.enabled = 1');
        $qb->andWhere('w.enabled = 1');
        $qb->groupBy('u.username');
        $qb->addGroupBy('yearMonth');
        $qb->addOrderBy('u.name');

        $qb->setParameters(['now' => date('Y-m-d H:i:s'), 'role_sid' => Role::ROLE_GUEST]);

        return $qb->getQuery()->getArrayResult();
    }

    public function groupParticipantsByName($participations)
    {
        $result = array();

        foreach ($participations as $participation) {
            /** @var Participant $participation */
            $name = $participation->getProfile()->getUsername();
            if (isset($result[$name])) {
                $result[$name][] = $participation;
            } else {
                $result[$name] = array($participation);
            }
        }

        return $result;
    }
}