<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RoleHierarchyTest extends KernelTestCase
{
    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        static::bootKernel();
    }

    /**
     * @test
     *
     * @testdox Admin role contains kitchen-staff, finance and user roles.
     */
    public function adminRole(): void
    {
        $roleHierarchyService = self::$container->get('security.role_hierarchy');
        $roleHierarchy = $roleHierarchyService->getReachableRoleNames(['ROLE_ADMIN']);

        $this->assertCount(4, $roleHierarchy);
        $this->assertContains('ROLE_ADMIN', $roleHierarchy);
        $this->assertContains('ROLE_KITCHEN_STAFF', $roleHierarchy);
        $this->assertContains('ROLE_FINANCE', $roleHierarchy);
        $this->assertContains('ROLE_USER', $roleHierarchy);
    }

    /**
     * @test
     *
     * @testdox Kitchen-staff role contains user role.
     */
    public function kitchenStaffRole(): void
    {
        $roleHierarchyService = self::$container->get('security.role_hierarchy');
        $roleHierarchy = $roleHierarchyService->getReachableRoleNames(['ROLE_KITCHEN_STAFF']);

        $this->assertCount(2, $roleHierarchy);
        $this->assertContains('ROLE_KITCHEN_STAFF', $roleHierarchy);
        $this->assertContains('ROLE_USER', $roleHierarchy);
    }

    /**
     * @test
     *
     * @testdox Finance role contains user role.
     */
    public function financeRole(): void
    {
        $roleHierarchyService = self::$container->get('security.role_hierarchy');
        $roleHierarchy = $roleHierarchyService->getReachableRoleNames(['ROLE_FINANCE']);

        $this->assertCount(2, $roleHierarchy);
        $this->assertContains('ROLE_FINANCE', $roleHierarchy);
        $this->assertContains('ROLE_USER', $roleHierarchy);
    }
}
