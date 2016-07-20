<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class MealRepository extends EntityRepository
{
    /**
     * @param string $date "YYYY-MM-DD"
     * @param string $dish slug of the dish
     * @return mixed|null
     * @throws \LogicException
     * @throws \InvalidArgumentException
     */
    public function findOneByDateAndDish($date, $dish)
    {
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/ims', $date)) {
            throw new \InvalidArgumentException('$date has to be a string of the format "YYYY-MM-DD".');
        }
        if ($date instanceof \DateTime) {
            $date = $date->format('Y-m-d');
        }

        $qb = $this->createQueryBuilder('m');

        // SELECT
        $qb->addSelect('d');

        // JOIN
        $qb->leftJoin('m.dish', 'd');

        // WHERE
        $qb->andWhere('m.dateTime >= :min_date');
        $qb->andWhere('m.dateTime <= :max_date');
        $qb->setParameter('min_date', $date.' 00:00:00');
        $qb->setParameter('max_date', $date.' 23:59:29');

        if (is_numeric($dish)) {
            $qb->andWhere('d.id = :dish');
        } else {
            $qb->andWhere('d.slug = :dish');
        }
        $qb->setParameter('dish', $dish);

        $result = $qb->getQuery()->execute();

        if (count($result) > 1) {
            throw new \LogicException('Found more then one meal matching the given requirements.');
        }

        return $result ? current($result) : null;
    }
}