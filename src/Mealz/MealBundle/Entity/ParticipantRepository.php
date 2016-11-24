<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Mealz\UserBundle\Entity\Profile;

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

    /**
     * get the Participants per Day
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
        $name1 = $participant1->isGuest() ? $participant1->getGuestName() : $participant1->getProfile()->getName();
        $name2 = $participant2->isGuest() ? $participant2->getGuestName() : $participant2->getProfile()->getName();
        $result = strcasecmp($name1, $name2);

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
     * find the Costs per Month per User
     * @return array
     */
    private function findCostsPerMonthPerUser()
    {
        $qb = $this->createQueryBuilder('p');
        $qb->select('u.username, u.name, u.firstName, SUBSTRING(m.dateTime, 1, 7) AS yearMonth, SUM(m.price) AS costs');
        $qb->leftJoin('p.meal', 'm');
        $qb->leftJoin('p.profile', 'u');
        $qb->leftJoin('m.day', 'd');
        $qb->leftJoin('d.week', 'w');
        /**
         * @TODO: optimize query. where clause costs a lot of time.
         */
        $qb->where('m.dateTime < :now');
        $qb->andWhere('d.enabled = 1');
        $qb->andWhere('w.enabled = 1');
        $qb->setParameter('now', date('Y-m-d H:i:s'));
        $qb->groupBy('u.username');
        $qb->addGroupBy('yearMonth');
        $qb->addOrderBy('u.name');

        return $qb->getQuery()->getArrayResult();
    }

    /**
     * Group the Participants by name
     * @param array $participations
     * @return array
     */
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

    /**
     * Get current participation for some user on particular day
     *
     * @param Profile $profile
     * @param string $date "YYYY-MM-DD"
     * @return array
     */
    public function getParticipationForProfile($profile, $date)
    {
        $options = array(
            'load_meal' => true,
            'load_profile' => true,
        );
        if (isset($profile)) {
            $options['load_profile'] = true;
        }
        $qb = $this->getQueryBuilderWithOptions($options);
        $qb->where('u.username = :profile');
        $qb->setParameter('profile', $profile->getUsername());
        $qb->andWhere('m.dateTime >= :min_date');
        $qb->andWhere('m.dateTime <= :max_date');
        $qb->setParameter('min_date', $date.' 00:00:00');
        $qb->setParameter('max_date', $date.' 23:59:29');

        return $qb->getQuery()->getArrayResult();
    }
}