<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\Repository;

use App\Mealz\MealBundle\Repository\BaseRepository;
use App\Mealz\UserBundle\Entity\Role;
use Doctrine\DBAL\ArrayParameterType;
use Override;

/**
 * @extends BaseRepository<int, Role>
 */
final class RoleRepository extends BaseRepository implements RoleRepositoryInterface
{
    /**
     * @return Role[]
     */
    #[Override]
    public function findBySID(array $sids): array
    {
        $queryBuilder = $this->createQueryBuilder('r');
        $queryBuilder->where($queryBuilder->expr()->in('r.sid', ':sids'));
        $queryBuilder->setParameter('sids', $sids, ArrayParameterType::STRING);

        $roles = $queryBuilder->getQuery()->getResult();

        return is_array($roles) ? $roles : [];
    }
}
