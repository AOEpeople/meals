<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\Tests\Repository;

use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\Entity\Role;
use App\Mealz\UserBundle\Entity\RoleRepository;
use App\Mealz\MealBundle\Tests\AbstractDatabaseTestCase;

class RoleRepositoryTest extends AbstractDatabaseTestCase
{
    protected RoleRepository $roleRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadRoles()
        ]);

        $this->roleRepository = $this->getDoctrine()->getRepository(Role::class);
    }

    /**
     * @test
     *
     * @dataProvider roleSIDProvider
     */
    public function findBySID(array $sids, array $expected): void
    {
        $roles = $this->roleRepository->findBySID($sids);

        $fetchedRoleSIDs = [];
        foreach ($roles as $role) {
            $this->assertInstanceOf(Role::class, $role);
            $fetchedRoleSIDs[] = $role->getSid();
        }

        $this->assertEquals(sort($expected), sort($fetchedRoleSIDs));
    }

    public function roleSIDProvider(): array
    {
        return [
            'one role' => [
                'sids' => ['ROLE_USER'],
                'expected' => ['ROLE_USER']
            ],
            'multiple roles' => [
                'sids' => ['ROLE_USER', 'ROLE_KITCHEN_STAFF'],
                'expected' => ['ROLE_USER', 'ROLE_KITCHEN_STAFF']
            ],
            'invalid SID; results no role' => [
                'sids' => ['NON_EXISTING_ROLE'],
                'expected' => []
            ],
            'mix of valid and invalid SIDs; returns roles for only valid SIDs' => [
                'sids' => ['NON_EXISTING_ROLE', 'ROLE_USER'],
                'success' => ['ROLE_USER']
            ],
        ];
    }
}
