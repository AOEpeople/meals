<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\EventListener\LocalisationListener;
use DateTime;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\Query\ResultSetMapping;
use Doctrine\ORM\QueryBuilder;

/**
 * @extends BaseRepository<int, Dish>
 */
class DishRepository extends BaseRepository implements DishRepositoryInterface
{
    protected array $defaultOptions = [
        'load_category' => true,
        'orderBy_category' => true,
        'load_disabled' => false,
        'load_disabled_variations' => false,
    ];

    private LocalisationListener $localisationListener;

    public function __construct(EntityManagerInterface $em, string $entityClass, LocalisationListener $listener)
    {
        parent::__construct($em, $entityClass);

        $this->localisationListener = $listener;
    }

    public function getSortedDishesQueryBuilder(array $options = []): QueryBuilder
    {
        $currentLocale = $this->localisationListener->getLocale();

        $options = array_merge($this->defaultOptions, $options);

        $query = $this->createQueryBuilder('d');

        // JOIN
        if (true === $options['load_category']) {
            $query->leftJoin('d.category', 'c');
        }

        // WHERE
        if (false === $options['load_disabled']) {
            $query->where('d.enabled = 1');
        }

        // ORDER BY
        if (true === $options['load_category'] && true === $options['orderBy_category']) {
            $query->orderBy('c.title_' . $currentLocale);
            $query->addOrderBy('d.title_' . $currentLocale);
        } else {
            $query->orderBy('d.title_' . $currentLocale, 'DESC');
        }

        return $query;
    }

    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function hasDishAssociatedMeals(Dish $dish): bool
    {
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('COUNT(m.dish)');
        $query->from(Meal::class, 'm');
        $query->where('m.dish = :dish');
        $query->setParameter('dish', $dish->getId(), Types::INTEGER);

        return 0 < $query->getQuery()->getSingleScalarResult();
    }

    /**
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function hasDishAssociatedCombiMealsInFuture(Dish $dish): bool
    {
        $today = new DateTime('today');
        $formattedDate = $today->format('Y-m-d') . ' 12:00:00.000000';
        if (true === $dish->hasVariations()) {
            $variations = $dish->getVariations();
            foreach ($variations as $variation) {
                if (true === $this->hasDishAssociatedCombiMealsInFuture($variation)) {
                    return true;
                }
            }
        }

        $mealRepo = $this->getEntityManager()->getRepository(Meal::class);
        $query = $mealRepo->createQueryBuilder('m2')
            ->select('COUNT(m2)')
            ->join('m2.day', 'd')
            ->join('m2.dish', 'g')
            ->join(Meal::class, 'm', 'WITH', 'm.day = d.id')
            ->join('m2.participants', 'p')
            ->where('g.slug = :slug')
            ->andWhere('m.dish = :dish_id')
            ->andWhere('m.dateTime >= :now')
            ->setParameter('slug', 'combined-dish')
            ->setParameter('dish_id', $dish->getId(), Types::INTEGER)
            ->setParameter('now', $formattedDate);

        return 0 < $query->getQuery()->getSingleScalarResult();
    }

    /**
     * Counts the number of Dish was taken in the last X Weeks.
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countNumberDishWasTaken(Dish $dish, string $countPeriod): int
    {
        // prepare sql statement counting all meals taken
        $query = $this->getEntityManager()->createQueryBuilder();
        $query->select('COUNT(m.dish)');
        $query->from(Meal::class, 'm');
        $query->where('m.dish = :dish');
        $query->andWhere($query->expr()->between('m.dateTime', ':date_from', ':date_to'));
        $query->setParameter('dish', $dish->getId(), Types::INTEGER);
        $query->setParameter('date_from', new DateTime($countPeriod), Types::DATETIME_MUTABLE);
        $query->setParameter('date_to', new DateTime('this week +6 days'), Types::DATETIME_MUTABLE);

        return (int) $query->getQuery()->getSingleScalarResult();
    }

    /**
     * Counts how many times each Dish was taken in the last X Weeks.
     *
     * Example Query:   select
     *                      count(dish_id), dish_id
     *                  from (
     *                      select
     *                          dish_id
     *                      from
     *                          meal
     *                      where
     *                          dateTime < "2023-07-05 23:59:00" and dateTime > "2023-06-05 00:00:00"
     *                  ) as D
     *                  group by dish_id;
     *
     * @throws NoResultException
     * @throws NonUniqueResultException
     */
    public function countNumberDishesWereTaken(string $countPeriod): array
    {
        $rsm = new ResultSetMapping();
        $rsm->addScalarResult('count(dish_id)', 'count', 'integer');
        $rsm->addScalarResult('dish_id', 'id');

        $startDate = new DateTime($countPeriod);
        $endDate = new DateTime('this week +6 days');

        $sql = 'select count(dish_id), dish_id ' .
                'from (' .
                    'select dish_id ' .
                    'from meal ' .
                    'where
                        dateTime < "' . date_format($endDate, 'Y-m-d H:i:s') .
                        '" and dateTime > "' . date_format($startDate, 'Y-m-d H:i:s') .
                        '") as D ' .
                'group by dish_id';

        $query = $this->getEntityManager()->createNativeQuery($sql, $rsm);
        $result = $query->getResult();
        $output = [];

        foreach ($result as $count) {
            $output[$count['id']] = $count['count'];
        }

        return $output;
    }
}
