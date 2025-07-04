<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\Service;

class RoleProvider
{
    /**
     * @return string[][]
     */
    public function getRoles(): array
    {
        return [
            ['title' => 'Kitchen Staff', 'sid' => 'ROLE_KITCHEN_STAFF'],
            ['title' => 'User', 'sid' => 'ROLE_USER'],
            ['title' => 'Guest', 'sid' => 'ROLE_GUEST'],
            ['title' => 'Administrator', 'sid' => 'ROLE_ADMIN'],
            ['title' => 'Finance Staff', 'sid' => 'ROLE_FINANCE'],
        ];
    }
}
