<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Repository;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Meal;
use DateTime;
use Doctrine\DBAL\Types\Types;
use InvalidArgumentException;
use LogicException;
use Override;

/**
 * @extends BaseRepository<int, Meal>
 */
final class MealRepository extends BaseRepository implements MealRepositoryInterface
{
    /**
     * @param string $dish           Dish slug
     * @param array  $userSelections already selected meals for that day
     *
     * @return mixed|null
     *
     * @throws LogicException
     * @throws InvalidArgumentException
     */
    #[Override]
    public function findOneByDateAndDish(DateTime $date, string $dish, array $userSelections = []): ?Meal
    {
        $queryBuilder = $this->createQueryBuilder('m');

        // SELECT
        $queryBuilder->addSelect('d');

        // JOIN
        $queryBuilder->leftJoin('m.dish', 'd');

        // WHERE
        $queryBuilder->andWhere('m.dateTime >= :min_date');
        $queryBuilder->andWhere('m.dateTime <= :max_date');
        $queryBuilder->setParameter('min_date', $date->format('Y-m-d 00:00:00'));
        $queryBuilder->setParameter('max_date', $date->format('Y-m-d 23:59:59'));

        $queryBuilder->andWhere('d.slug = :slug');
        $queryBuilder->setParameter('slug', $dish);

        $result = $queryBuilder->getQuery()->execute();

        if (count($result) > 1) {
            $results = $result;

            // this is actually a logical error, when there are 2 identical Dish for same day, but we try to handle it
            foreach ($results as $key => $meal) {
                if (in_array($meal->getId(), $userSelections)) {
                    unset($result[$key]);
                }
            }
        }

        return $result ? current($result) : null;
    }

    /**
     * @return Meal[]
     */
    #[Override]
    public function findAllOn(DateTime $date): array
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder
            ->where('m.dateTime >= :startTime')
            ->andWhere('m.dateTime <= :endTime')
            ->setParameter('startTime', (clone $date)->setTime(0, 0), Types::DATETIME_MUTABLE)
            ->setParameter('endTime', (clone $date)->setTime(23, 59), Types::DATETIME_MUTABLE);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @return Meal[]
     */
    #[Override]
    public function findAllBetween(DateTime $startDate, DateTime $endDate): array
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder
            ->where('m.dateTime >= :startTime')
            ->andWhere('m.dateTime <= :endTime')
            ->setParameter('startTime', (clone $startDate)->setTime(0, 0), Types::DATETIME_MUTABLE)
            ->setParameter('endTime', (clone $endDate)->setTime(23, 59), Types::DATETIME_MUTABLE);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Created for Test with Dish variations.
     *
     * @psalm-return list<array{id: int}>
     */
    #[Override]
    public function getMealsOnADayWithVariationOptions(): array
    {
        $entityManager = $this->getEntityManager();
        $query = $entityManager->createQuery(
            'SELECT m.id
                FROM MealzMealBundle:Meal m
                WHERE m.day IN
                  (SELECT IDENTITY (ml.day) FROM MealzMealBundle:Meal ml GROUP BY ml.day HAVING COUNT(ml.day)>2)'
        );

        return $query->getResult();
    }

    /**
     * Returns all meals that are going to take place in the future.
     *
     * @return Meal[]
     */
    #[Override]
    public function getFutureMeals(): array
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->where('m.dateTime >= :now');
        $queryBuilder->setParameter(':now', new DateTime('now'), Types::DATETIME_MUTABLE);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Returns all meals that are going to took place in the past.
     *
     * @return Meal[]
     */
    #[Override]
    public function getOutdatedMeals(): array
    {
        $queryBuilder = $this->createQueryBuilder('m');
        $queryBuilder->where('m.dateTime <= :now');
        $queryBuilder->setParameter(':now', new DateTime('now'), Types::DATETIME_MUTABLE);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Returns all meals that are going to take place in the future but aren't available to join/leave anymore.
     *
     * @return Meal[]
     */
    #[Override]
    public function getLockedMeals(): array
    {
        $entityManager = $this->getEntityManager();
        $queryBuilder = $entityManager->createQueryBuilder();
        $queryBuilder->select('m')
            ->from(Meal::class, 'm')
            ->innerJoin('m.day', 'd')
            ->where('d.lockParticipationDateTime < :now')
            ->andWhere('m.dateTime > :now')
            ->orderBy('m.dateTime', 'DESC');

        $queryBuilder->setParameter(':now', new DateTime('now'), Types::DATETIME_MUTABLE);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * Returns all CombiMeals that are in the future and contain a specific dish.
     *
     * @return Meal[]
     */
    #[Override]
    public function getFutureMealsForDish(Dish $dish): array
    {
        $queryBuilder = $this->createQueryBuilder('m')
            ->select('m')
            ->join('m.day', 'd')
            ->andWhere('m.dish = :dish_id')
            ->andWhere('m.dateTime >= :now')
            ->setParameter('dish_id', $dish->getId(), Types::INTEGER)
            ->setParameter('now', new DateTime('now'), Types::DATETIME_MUTABLE);

        return $queryBuilder->getQuery()->getResult();
    }
}
