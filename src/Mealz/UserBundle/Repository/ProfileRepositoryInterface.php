<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\Repository;

use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\Persistence\ObjectRepository;

/**
 * @template-extends ObjectRepository<Profile>
 */
interface ProfileRepositoryInterface extends ObjectRepository
{
    /**
     * Find all profiles except the one existing in $usernames.
     *
     * @param string[] $usernames
     *
     * @return Profile[]
     */
    public function findAllExcept(array $usernames): array;
}
