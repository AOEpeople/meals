<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\Repository;

use App\Mealz\UserBundle\Entity\Role;
use Doctrine\Persistence\ObjectRepository;

/**
 * @template-extends ObjectRepository<Role>
 */
interface RoleRepositoryInterface extends ObjectRepository
{
    /**
     * @param string[] $sids
     *
     * @return Role[]
     */
    public function findBySID(array $sids): array;
}
