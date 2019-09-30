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

    /**
     * default options for database queries
     * @var array
     */
    protected $defaultOptions = array(
        'load_meal' => false,
        'load_profile' => true,
        'load_roles' => false,
    );

    /**
     * @param \DateTime $minDate
     * @param \DateTime $maxDate
     * @param Profile|null $profile
     * @param array $options
     * @return mixed
     */
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
                'load_roles' => true,
            )
        );
        if ($profile instanceof Profile) {
            $options['load_profile'] = true;
        }
        $queryBuilder = $this->getQueryBuilderWithOptions($options);

        $minDate = clone $minDate;
        $minDate->setTime(0, 0, 0);
        $maxDate = clone $maxDate;
        $maxDate->setTime(23, 59, 59);

        $queryBuilder->andWhere('m.dateTime >= :minDate');
        $queryBuilder->andWhere('m.dateTime <= :maxDate');
        $queryBuilder->setParameter('minDate', $minDate);
        $queryBuilder->setParameter('maxDate', $maxDate);

        if ($profile instanceof Profile) {
            $queryBuilder->andWhere('u.username = :username');
            $queryBuilder->setParameter('username', $profile->getUsername());
        }

        $queryBuilder->orderBy('u.name', 'ASC');

        $participants = $queryBuilder->getQuery()->execute();

        return $this->sortParticipantsByName($participants);
    }

    /**
     * helper function to sort participants by their name or guest name
     * @param mixed $participants
     * @return mixed
     */
    public function sortParticipantsByName($participants)
    {
        usort($participants, array($this, 'compareNameOfParticipants'));

        return $participants;
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
        $queryBuilder = $this->getQueryBuilderWithOptions(
            [
                'load_meal' => true,
                'load_profile' => false,
            ]
        );

        $queryBuilder->select('SUM(m.price) as blubber');
        $queryBuilder->leftJoin('m.day', 'day');
        $queryBuilder->leftJoin('day.week', 'w');
        $queryBuilder->andWhere('p.profile = :user');
        $queryBuilder->setParameter('user', $username);
        $queryBuilder->andWhere('p.costAbsorbed = 0');
        $queryBuilder->andWhere('day.enabled = 1');
        $queryBuilder->andWhere('w.enabled = 1');
        $queryBuilder->andWhere('m.dateTime <= :now');
        $queryBuilder->setParameter('now', new \DateTime());

        return floatval($queryBuilder->getQuery()->getSingleScalarResult());
    }

    /**
     * @param Profile $profile
     * @param int $limit
     * @return Participant[]
     */
    public function getLastAccountableParticipations(Profile $profile, $limit = null)
    {
        $queryBuilder = $this->getQueryBuilderWithOptions(
            [
                'load_meal' => true,
                'load_profile' => false,
            ]
        );

        $queryBuilder->andWhere('p.profile = :user');
        $queryBuilder->setParameter('user', $profile);
        $queryBuilder->andWhere('p.costAbsorbed = :costAbsorbed');
        $queryBuilder->setParameter('costAbsorbed', false);
        $queryBuilder->andWhere('m.dateTime <= :now');
        $queryBuilder->setParameter('now', new \DateTime());

        $queryBuilder->orderBy('m.dateTime', 'desc');
        if (is_int($limit) === true) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @return array
     */
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
            if (array_key_exists($username, $result) === true) {
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
     * @param mixed $participations
     * @return array
     */
    public function groupParticipantsByName($participations)
    {
        $result = array();

        foreach ($participations as $participation) {
            /** @var Participant $participation */
            $name = $participation->getProfile()->getUsername();
            if (array_key_exists($name, $result) === true) {
                $result[$name][] = $participation;
            } else {
                $result[$name] = array($participation);
            }
        }

        return $result;
    }

    /**
     * @param array $options
     * @return mixed
     */
    public function getParticipantsOnCurrentDay($options = array())
    {
        $options = array_merge(
            $options,
            array(
                'load_meal' => true,
                'load_profile' => true,
            )
        );

        $queryBuilder = $this->getQueryBuilderWithOptions($options);
        $queryBuilder->andWhere('m.dateTime LIKE :today');
        $queryBuilder->setParameter(':today', date('Y-m-d%'));

        $queryBuilder->orderBy('u.name', 'ASC');

        $participants = $queryBuilder->getQuery()->execute();

        return $this->sortParticipantsByName($participants);
    }

    /**
     * @param Participant $participant1
     * @param Participant $participant2
     * @return int
     */
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
     * @param $options
     * @return \Doctrine\ORM\QueryBuilder
     */
    protected function getQueryBuilderWithOptions($options)
    {
        $options = array_merge($this->defaultOptions, $options);

        $queryBuilder = $this->createQueryBuilder('p');

        // SELECT
        $select = 'p';
        if (array_key_exists('load_meal', $options) === true) {
            $select .= ',m,d';
        }
        if (array_key_exists('load_profile', $options) === true) {
            $select .= ',u';
            if (array_key_exists('load_roles', $options) === true) {
                $select .= ',r';
            }
        }
        $queryBuilder->select($select);

        // JOIN
        if (array_key_exists('load_meal', $options) === true) {
            $queryBuilder->leftJoin('p.meal', 'm');
            $queryBuilder->leftJoin('m.dish', 'd');
        }
        if (array_key_exists('load_profile', $options) === true) {
            $queryBuilder->leftJoin('p.profile', 'u');
            if (array_key_exists('load_roles', $options) === true) {
                $queryBuilder->leftJoin('u.roles', 'r');
            }
        }

        return $queryBuilder;
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
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->select('u.username, u.name, u.firstName, SUBSTRING(m.dateTime, 1, 7) AS yearMonth, SUM(m.price) AS costs');
        $queryBuilder->leftJoin('p.meal', 'm');
        $queryBuilder->leftJoin('p.profile', 'u');
        $queryBuilder->leftJoin('u.roles', 'r');
        $queryBuilder->leftJoin('m.day', 'd');
        $queryBuilder->leftJoin('d.week', 'w');
        /**
         * @TODO: optimize query. where clause costs a lot of time.
         */
        $queryBuilder->where('p.costAbsorbed = 0');
        $queryBuilder->andWhere(
            $queryBuilder->expr()->orX(
                $queryBuilder->expr()->isNull('r.sid'),
                $queryBuilder->expr()->neq('r.sid', ':role_sid')
            )
        );
        $queryBuilder->andWhere('m.dateTime < :now');
        $queryBuilder->andWhere('d.enabled = 1');
        $queryBuilder->andWhere('w.enabled = 1');
        $queryBuilder->groupBy('u.username');
        $queryBuilder->addGroupBy('yearMonth');
        $queryBuilder->addOrderBy('u.name');

        $queryBuilder->setParameters(['now' => date('Y-m-d H:i:s'), 'role_sid' => Role::ROLE_GUEST]);

        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * Returns meals that are being offered, ordered by the time they were offered
     * @param $mealId
     * @return array
     */
    public function findByOffer($mealId)
    {
        $queryBuilder = $this->createQueryBuilder('a');
        $queryBuilder->where(
            $queryBuilder->expr()->not(
                $queryBuilder->expr()->eq('a.' . 'offeredAt', '?1')
            ),
            $queryBuilder->expr()->eq('a.' . 'meal', '?2')
        );
        $queryBuilder->setParameter(1, 0);
        $queryBuilder->setParameter(2, $mealId);
        $queryBuilder->orderBy('a.offeredAt', 'asc');

        return $queryBuilder->getQuery()
            ->getResult();
    }

    /**
     * Returns an array with all the pending participants on the given date.
     * @param \DateTime $dateTime
     * @return array
     */
    public function getPendingParticipants(\DateTime $dateTime)
    {
        $minDate = new \DateTime(date('d-m-Y', date_timestamp_get($dateTime)) . '00:00');
        $maxDate = new \DateTime(date('d-m-Y', date_timestamp_get($dateTime)) . '23:59');

        $options = array('load_meal' => true);
        $queryBuilder = $this->getQueryBuilderWithOptions($options);

        $queryBuilder->andWhere('m.dateTime >= :minDate');
        $queryBuilder->andWhere('m.dateTime <= :maxDate');
        $queryBuilder->setParameter('minDate', $minDate);
        $queryBuilder->setParameter('maxDate', $maxDate);

        $queryBuilder->andWhere('p.offeredAt != 0');

        return $queryBuilder->getQuery()
            ->getResult();
    }
}
