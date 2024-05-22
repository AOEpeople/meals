<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Helper\ParticipationHelper;
use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\UserBundle\Entity\Role;
use DateTime;
use Doctrine\DBAL\ParameterType;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends BaseRepository<Participant>
 */
class ParticipantRepository extends BaseRepository implements ParticipantRepositoryInterface
{
    /**
     * default options for database queries.
     */
    protected array $defaultOptions = [
        'load_meal' => false,
        'load_profile' => true,
        'load_roles' => false,
    ];

    protected ParticipationHelper $participationHelper;

    public function __construct(
        EntityManagerInterface $entityManager, string $entityClass, ParticipationHelper $participationHelper
    ) {
        parent::__construct($entityManager, $entityClass);

        $this->participationHelper = $participationHelper;
    }

    /**
     * @return Participant[]
     */
    public function getParticipantsOnDays(
        DateTime $startDate,
        DateTime $endDate,
        ?Profile $profile = null
    ): array {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder
            ->select(['p', 'm', 'up'])
            ->join('p.meal', 'm')
            ->join('p.profile', 'up')
            ->where('m.dateTime >= :startDate')
            ->andWhere('m.dateTime <= :endDate')
            ->orderBy('up.name', 'ASC')
            ->addOrderBy('m.dateTime', 'ASC')
            ->setParameters([
                'startDate' => (clone $startDate),
                'endDate' => (clone $endDate),
            ]);

        if (null !== $profile) {
            $queryBuilder
                ->andWhere('p.profile = :profile_id')
                ->setParameter('profile_id', $profile->getUsername());
        }

        return $queryBuilder->getQuery()->execute();
    }

    public function getTotalCost(string $username): float
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder
            ->select('SUM(m.price) as total_cost')
            ->leftJoin('p.meal', 'm')
            ->leftJoin('p.profile', 'up')
            ->leftJoin('m.day', 'd')
            ->leftJoin('d.week', 'w')
            ->where('p.profile = :user')
            ->andWhere('m.dateTime <= :now')
            ->andWhere('p.costAbsorbed = 0')
            ->andWhere('d.enabled = 1')
            ->andWhere('w.enabled = 1');
        $queryBuilder->setParameter('user', $username, Types::STRING);
        $queryBuilder->setParameter('now', new DateTime(), Types::DATETIME_MUTABLE);

        $result = $queryBuilder->getQuery()->getResult();
        if ($result && is_array($result) && count($result) >= 1) {
            return (float) ($result[0]['total_cost'] ?? 0.0);
        }

        return 0.0;
    }

    /**
     * @return Participant[]
     */
    public function getLastAccountableParticipations(Profile $profile, ?int $limit = null): array
    {
        $queryBuilder = $this->getQueryBuilderWithOptions(
            [
                'load_meal' => true,
                'load_profile' => false,
            ]
        );

        $queryBuilder->andWhere('p.profile = :user');
        $queryBuilder->setParameter('user', $profile->getUsername(), Types::STRING);
        $queryBuilder->andWhere('p.costAbsorbed = :costAbsorbed');
        $queryBuilder->setParameter('costAbsorbed', false, Types::BOOLEAN);
        $queryBuilder->andWhere('m.dateTime <= :now');
        $queryBuilder->setParameter('now', new DateTime(), Types::DATETIME_MUTABLE);

        $queryBuilder->orderBy('m.dateTime', 'desc');
        if (true === is_int($limit)) {
            $queryBuilder->setMaxResults($limit);
        }

        return $queryBuilder->getQuery()->execute();
    }

    /**
     * @return ((false|int|mixed)[][]|mixed)[][]
     *
     * @psalm-return array<array{name?: mixed, firstName?: mixed, hidden?: mixed, costs: list<array{timestamp: false|int, costs: mixed}>}>
     */
    public function findCostsGroupedByUserGroupedByMonth(): array
    {
        $costs = $this->findCostsPerMonthPerUser();

        $result = [];

        foreach ($costs as $cost) {
            $username = $cost['username'];
            $timestamp = strtotime($cost['yearMonth']);
            $costByMonth = [
                'timestamp' => $timestamp,
                'costs' => $cost['costs'],
            ];
            if (true === array_key_exists($username, $result)) {
                $result[$username]['costs'][] = $costByMonth;
            } else {
                $result[$username] = [
                    'name' => $cost['name'],
                    'firstName' => $cost['firstName'],
                    'hidden' => $cost['hidden'],
                    'costs' => [$costByMonth],
                ];
            }
        }

        return $result;
    }

    /**
     * @param Participant[] $participants
     *
     * @return array<string, list<Participant>>
     */
    public function groupParticipantsByName(array $participants): array
    {
        $result = [];

        foreach ($participants as $participant) {
            $name = $participant->getProfile()->getUsername();
            if (isset($result[$name])) {
                $result[$name][] = $participant;
            } else {
                $result[$name] = [$participant];
            }
        }

        ksort($result);

        return $result;
    }

    /**
     * @psalm-return array<string, array<string, array{booked: non-empty-list<int>}>>
     */
    public function findAllGroupedBySlotAndProfileID(DateTime $date, bool $getProfile = false): array
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder
            ->select(['p', 'm', 's', 'up'])
            ->leftJoin('p.slot', 's')
            ->join('p.profile', 'up')
            ->join('p.meal', 'm', Join::WITH, 'm.dateTime >= :startTime AND m.dateTime <= :endTime')
            ->orderBy('s.order', 'ASC')
            ->addOrderBy('up.name')
            ->addOrderBy('up.firstName')
            ->setParameters([
                'startTime' => (clone $date)->setTime(0, 0),
                'endTime' => (clone $date)->setTime(23, 59),
            ]);

        $result = $queryBuilder->getQuery()->getResult();
        if (!is_array($result) || empty($result)) {
            return [];
        }

        return $this->participationHelper->groupBySlotAndProfileID($result, $getProfile);
    }

    /**
     * @return Participant[]
     */
    public function getParticipantsByDay(DateTime $date, array $options = []): array
    {
        $options = array_merge(
            [
                'load_meal' => true,
                'load_profile' => true,
            ],
            $options,
        );

        $queryBuilder = $this->getQueryBuilderWithOptions($options);
        $queryBuilder->andWhere('m.dateTime LIKE :date');
        $queryBuilder->setParameter(':date', $date, Types::DATETIME_MUTABLE);

        $queryBuilder->orderBy('u.name', 'ASC');

        $participants = $queryBuilder->getQuery()->execute();

        return $participants;
    }

    /**
     * @return Participant[]
     */
    public function getParticipantsOnCurrentDay(array $options = []): array
    {
        $options = array_merge(
            $options,
            [
                'load_meal' => true,
                'load_profile' => true,
            ]
        );

        $queryBuilder = $this->getQueryBuilderWithOptions($options);
        $queryBuilder->andWhere('m.dateTime LIKE :today');
        $queryBuilder->setParameter(':today', date('Y-m-d%'));

        $queryBuilder->orderBy('u.name', 'ASC');

        $participants = $queryBuilder->getQuery()->execute();

        return $this->participationHelper->sortParticipantsByName($participants);
    }

    protected function getQueryBuilderWithOptions(array $options): QueryBuilder
    {
        $options = array_merge($this->defaultOptions, $options);

        $queryBuilder = $this->createQueryBuilder('p');

        // SELECT
        $select = 'p';
        if (true === array_key_exists('load_meal', $options) && true === $options['load_meal']) {
            $select .= ',m,d';
        }
        if (true === array_key_exists('load_profile', $options)) {
            $select .= ',u';
            if (true === array_key_exists('load_roles', $options)) {
                $select .= ',r';
            }
        }
        $queryBuilder->select($select);

        // JOIN
        if (true === array_key_exists('load_meal', $options)) {
            $queryBuilder->leftJoin('p.meal', 'm');
            $queryBuilder->leftJoin('m.dish', 'd');
        }
        if (true === array_key_exists('load_profile', $options)) {
            $queryBuilder->leftJoin('p.profile', 'u');
            if (true === array_key_exists('load_roles', $options)) {
                $queryBuilder->leftJoin('u.roles', 'r');
            }
        }

        return $queryBuilder;
    }

    /**
     * Gets the aggregated monthly cost of all the participants.
     *
     * Guests are currently excluded from the result.
     */
    private function findCostsPerMonthPerUser(): array
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->select('u.username, u.name, u.firstName, u.hidden, SUBSTRING(m.dateTime, 1, 7) AS yearMonth, SUM(m.price) AS costs');
        $queryBuilder->leftJoin('p.meal', 'm');
        $queryBuilder->leftJoin('p.profile', 'u');
        $queryBuilder->leftJoin('u.roles', 'r');
        $queryBuilder->leftJoin('m.day', 'd');
        $queryBuilder->leftJoin('d.week', 'w');
        /*
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
     * Returns count of booked meals available to be taken by others on a given $date.
     */
    public function getOfferCount(DateTime $date): int
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder
            ->join('p.meal', 'm', Join::WITH, 'm.dateTime >= :startTime AND m.dateTime <= :endTime')
            ->select('count(p.id) AS count')
            ->where('p.offeredAt != 0')
            ->setParameters([
                'startTime' => (clone $date)->setTime(0, 0),
                'endTime' => (clone $date)->setTime(23, 59),
            ]);

        $result = $queryBuilder->getQuery()->getArrayResult();

        return $result[0]['count'] ?? 0;
    }

    /**
     * Returns count of booked meals available to be taken over by others.
     */
    public function getOfferCountByMeal(Meal $meal): int
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder
            ->join('p.meal', 'm', Join::WITH, 'm.id = :mealId')
            ->select('count(p.id) AS count')
            ->where('p.offeredAt > 0')
            ->setParameter('mealId', $meal->getId(), ParameterType::INTEGER);

        $result = $queryBuilder->getQuery()->getArrayResult();

        return $result[0]['count'] ?? 0;
    }

    /**
     * Returns true if the specified user is offering the specified meal.
     */
    public function isOfferingMeal(Profile $profile, Meal $meal): bool
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder
            ->innerJoin('App\Mealz\MealBundle\Entity\Meal', 'm', Join::WITH, 'm = p.meal')
            ->innerJoin('App\Mealz\UserBundle\Entity\Profile', 'u', Join::WITH, 'u = p.profile')
            ->select('count(p.id) AS count')
            ->where(
                $queryBuilder->expr()->gt('p.offeredAt', 0),
                $queryBuilder->expr()->eq('m.id', ':mealId'),
                $queryBuilder->expr()->like('u.username', ':profileId')
            )
            ->setParameters([
                'mealId' => $meal->getId(),
                'profileId' => $profile->getUsername(),
            ]);
        $result = $queryBuilder->getQuery()->getArrayResult();

        return $result[0]['count'] > 0;
    }

    /**
     * Gets number of participants (booked meals) per slot on a given $date.
     *
     * @psalm-return list<array{date: DateTime, slot: int, count: int}>
     */
    public function getCountBySlots(DateTime $startDate, DateTime $endDate): array
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder
            ->select(['m.dateTime AS date', 'IDENTITY(p.slot) as slot_id'])
            ->join('p.meal', 'm', Join::WITH, 'm.dateTime >= :startDate AND m.dateTime <= :endDate')
            ->where($queryBuilder->expr()->isNotNull('p.slot'))
            ->groupBy('m.dateTime')
            ->addGroupBy('p.profile')
            ->addGroupBy('p.slot')
            ->setParameters([
                'startDate' => (clone $startDate)->setTime(0, 0),
                'endDate' => (clone $endDate)->setTime(23, 59, 59),
            ]);

        $result = [];

        // count number of participants per day per slot
        /** @var array{date: DateTime, slot_id: int} $item */
        foreach ($queryBuilder->getQuery()->getArrayResult() as $item) {
            $k = $item['date']->format('Ymd') . $item['slot_id'];

            if (isset($result[$k])) {
                ++$result[$k]['count'];
            } else {
                $result[$k] = [
                    'date' => $item['date'],
                    'slot' => (int) $item['slot_id'],
                    'count' => 1,
                ];
            }
        }

        return array_values($result);
    }

    /**
     * Gets number of participants booked for a slot on a given day.
     *
     * @psalm-return 0|positive-int
     */
    public function getCountBySlot(Slot $slot, DateTime $date): int
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder
            ->select(['p.id'])
            ->join('p.meal', 'm', Join::WITH, 'm.dateTime >= :startDate AND m.dateTime <= :endDate')
            ->where($queryBuilder->expr()->eq('p.slot', $slot->getId()))
            ->groupBy('m.dateTime')
            ->addGroupBy('p.profile')
            ->addGroupBy('p.slot')
            ->setParameters([
                'startDate' => (clone $date)->setTime(0, 0),
                'endDate' => (clone $date)->setTime(23, 59, 59),
            ]);

        return count($queryBuilder->getQuery()->getArrayResult());
    }

    /**
     * Gets number of participants booked for a certain meal.
     *
     * @psalm-return 0|positive-int
     */
    public function getCountByMeal(Meal $meal): int
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder
            ->select(['p.id'])
            ->where($queryBuilder->expr()->eq('p.meal', $meal->getId()));

        return count($queryBuilder->getQuery()->getArrayResult());
    }

    public function updateSlot(Profile $profile, DateTime $date, Slot $slot): void
    {
        // get all participant IDs with profile $profile that enrolled for a meal on $date
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder
            ->select('p.id')
            ->join('p.meal', 'm', Join::WITH, 'm.dateTime >= :startTime AND m.dateTime <= :endTime')
            ->where('p.profile = :profileID')
            ->setParameters([
                'startTime' => (clone $date)->setTime(0, 0),
                'endTime' => (clone $date)->setTime(23, 59),
                'profileID' => $profile->getUsername(),
            ]);

        $partIDs = $queryBuilder->getQuery()->getSingleColumnResult();
        if (1 > count($partIDs)) {
            return;
        }

        // update slot for selected participant IDs
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder
            ->update()
            ->set('p.slot', $slot->getId())
            ->where($queryBuilder->expr()->in('p.id', $partIDs));
        $queryBuilder->getQuery()->execute();
    }

    /**
     * Removes all future ordered meals for a given profile.
     */
    public function removeFutureMealsByProfile(Profile $profile): void
    {
        // Get tomorrow's date
        $tomorrow = new DateTime('tomorrow');

        // Get all participants ID's through a join onto meals table
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->leftJoin('p.meal', 'm');
        $queryBuilder->andWhere('p.profile = :profile');
        $queryBuilder->setParameter('profile', $profile->getUsername());
        $queryBuilder->andWhere('m.dateTime >= :tomorrow');
        $queryBuilder->setParameter('tomorrow', $tomorrow->format('Y-m-d H:i:s'));
        $queryBuilder->select('p.id');
        $meals = $queryBuilder->getQuery()->getArrayResult();

        // Remove the ID's form the participants table
        if (count($meals)) {
            $this->createQueryBuilder('participant')
                ->where('participant.id in (:ids)')
                ->setParameter('ids', $meals)
                ->delete()
                ->getQuery()
                ->execute();
        }
    }

    public function getParticipationsOfSlot(Slot $slot): array
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.meal', 'm')
            ->andWhere('p.slot = :slotId')
            ->setParameter('slotId', $slot->getId())
            ->andWhere('m.dateTime >= :firstDay')
            ->setParameter('firstDay', new DateTime('monday this week'), Types::DATETIME_MUTABLE);

        $participations = $queryBuilder->getQuery()->execute();

        return $participations;
    }
}
