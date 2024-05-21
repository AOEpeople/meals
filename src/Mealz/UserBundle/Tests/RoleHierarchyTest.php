<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class RoleHierarchyTest extends KernelTestCase
{
    /**
     * {@inheritDoc}
     */
    protected function setUp(): void
    {
        static::bootKernel();
    }

    /**
     * @testdox Admin role contains kitchen-staff, finance and user roles.
     */
    public function testAdminRole(): void
    {
        $roleHierarchyService = self::getContainer()->get('security.role_hierarchy');
        $roleHierarchy = $roleHierarchyService->getReachableRoleNames(['ROLE_ADMIN']);

        $this->assertCount(4, $roleHierarchy);
        $this->assertContains('ROLE_ADMIN', $roleHierarchy);
        $this->assertContains('ROLE_KITCHEN_STAFF', $roleHierarchy);
        $this->assertContains('ROLE_FINANCE', $roleHierarchy);
        $this->assertContains('ROLE_USER', $roleHierarchy);
    }

    /**
     * @testdox Kitchen-staff role contains user role.
     */
    public function testKitchenStaffRole(): void
    {
        $roleHierarchyService = self::getContainer()->get('security.role_hierarchy');
        $roleHierarchy = $roleHierarchyService->getReachableRoleNames(['ROLE_KITCHEN_STAFF']);

        $this->assertCount(2, $roleHierarchy);
        $this->assertContains('ROLE_KITCHEN_STAFF', $roleHierarchy);
        $this->assertContains('ROLE_USER', $roleHierarchy);
    }

    /**
     * @testdox Finance role contains user role.
     */
    public function testFinanceRole(): void
    {
        $roleHierarchyService = self::getContainer()->get('security.role_hierarchy');
        $roleHierarchy = $roleHierarchyService->getReachableRoleNames(['ROLE_FINANCE']);

        $this->assertCount(2, $roleHierarchy);
        $this->assertContains('ROLE_FINANCE', $roleHierarchy);
        $this->assertContains('ROLE_USER', $roleHierarchy);
    }
}
