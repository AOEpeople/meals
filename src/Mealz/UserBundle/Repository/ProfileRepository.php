<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\Repository;

use App\Mealz\MealBundle\Repository\BaseRepository;
use App\Mealz\UserBundle\Entity\Profile;

/**
 * @extends BaseRepository<int, Profile>
 */
class ProfileRepository extends BaseRepository implements ProfileRepositoryInterface
{
    public function findAllExcept($usernames): array
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder->addSelect('r');
        $queryBuilder->leftJoin('p.roles', 'r');
        $queryBuilder->where($queryBuilder->expr()->notIn('p.username', ':usernames'));
        $queryBuilder->setParameter(':usernames', $usernames);

        return $queryBuilder->getQuery()->getResult();
    }
}
