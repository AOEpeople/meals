<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\Service;

use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\UserBundle\Entity\Role;
use App\Mealz\UserBundle\Repository\ProfileRepositoryInterface;
use App\Mealz\UserBundle\Repository\RoleRepositoryInterface;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use RuntimeException;

class ProfileService
{
    private EntityManagerInterface $em;
    private ProfileRepositoryInterface $profileRepository;
    private RoleRepositoryInterface $roleRepository;

    public function __construct(
        EntityManagerInterface $em,
        ProfileRepositoryInterface $profileRepo,
        RoleRepositoryInterface $roleRepository,
    ) {
        $this->em = $em;
        $this->profileRepository = $profileRepo;
        $this->roleRepository = $roleRepository;
    }

    public function createGuest(string $firstName, string $lastName, ?string $company): array
    {
        $existingProfile = $this->profileExists($firstName, $lastName, $company);
        if (null !== $existingProfile) {
            return [
                'profile' => $existingProfile,
                'status' => 'existing',
            ];
        } else {
            $profile = new Profile();
            $profile->setUsername(sprintf('%s.%s_%s', $firstName, $lastName, (new DateTime())->format('Y-m-d')));
            $profile->setFirstName($firstName);
            $profile->setName($lastName);
            if (0 < strlen($company)) {
                $profile->setCompany($company);
            }
            $profile->addRole($this->getGuestRole());

            $this->em->persist($profile);
            $this->em->flush();

            return [
                'profile' => $profile,
                'status' => 'new',
            ];
        }
    }

    private function profileExists(string $firstName, string $lastName, string $company): ?Profile
    {
        $profile = $this->profileRepository->findOneBy([
            'firstName' => $firstName,
            'name' => $lastName,
            'company' => $company,
            'hidden' => false,
        ]);

        return $profile && $profile->isGuest() ? $profile : null;
    }

    private function getGuestRole(): Role
    {
        $guestRole = $this->roleRepository->findOneBy(['sid' => Role::ROLE_GUEST]);
        if (null === $guestRole) {
            throw new RuntimeException('role not found: ' . Role::ROLE_GUEST);
        }

        return $guestRole;
    }
}
