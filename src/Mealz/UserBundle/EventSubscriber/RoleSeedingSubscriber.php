<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\EventSubscriber;

use App\Mealz\UserBundle\Service\RoleSeeder;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\KernelEvents;

class RoleSeedingSubscriber implements EventSubscriberInterface
{
    private RoleSeeder $roleSeeder;

    public function __construct(RoleSeeder $roleSeeder)
    {
        $this->roleSeeder = $roleSeeder;
    }

    public function onKernelRequest(): void
    {
        $this->roleSeeder->seedRolesIfEmpty();
    }

    public static function getSubscribedEvents(): array
    {
        return [
            KernelEvents::REQUEST => ['onKernelRequest', 1],
        ];
    }
}
