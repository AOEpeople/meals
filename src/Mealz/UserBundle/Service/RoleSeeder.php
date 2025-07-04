<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\Service;

use App\Mealz\UserBundle\Entity\Role;
use App\Mealz\UserBundle\Repository\RoleRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class RoleSeeder
{
    private RoleRepositoryInterface $roleRepo;
    private RoleProvider $roleProvider;
    private EntityManagerInterface $em;
    private LoggerInterface $logger;

    public function __construct(
        RoleRepositoryInterface $roleRepository,
        RoleProvider $roleProvider,
        EntityManagerInterface $entityManager,
        LoggerInterface $loggerInterface,
    ) {
        $this->roleRepo = $roleRepository;
        $this->roleProvider = $roleProvider;
        $this->em = $entityManager;
        $this->logger = $loggerInterface;
    }

    public function seedRolesIfEmpty(): void
    {
        if (0 === count($this->roleRepo->findAll())) {
            $this->logger->info('Adding roles because db is missing them');
            foreach ($this->roleProvider->getRoles() as $role) {
                $roleObj = new Role();
                $roleObj->setTitle($role['title'])
                        ->setSid($role['sid']);
                $this->em->persist($roleObj);
                $this->logger->info('ROLE_ADDED: ' . $role['title']);
            }

            $this->em->flush();
        }
    }
}
