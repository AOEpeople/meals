<?php

namespace App\Mealz\UserBundle\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use App\Mealz\UserBundle\Entity\ProfileRepository;

class InteractiveLoginListener
{
    private EntityManagerInterface $entityManager;

    private ProfileRepository $profileRepository;

    public function __construct(EntityManagerInterface $entityManager, ProfileRepository $profileRepository)
    {
        $this->entityManager = $entityManager;
        $this->profileRepository = $profileRepository;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        $profile = $this->profileRepository->findOneBy(array('username' => $user->getUsername()));
        if ($profile !== null) {
            $profile->setHidden(false);

            $this->entityManager->persist($profile);
            $this->entityManager->flush();
        }
    }
}