<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\EventSubscriber;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use App\Mealz\UserBundle\Entity\ProfileRepository;
use Symfony\Component\Security\Http\SecurityEvents;

class InteractiveLoginSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;

    private ProfileRepository $profileRepository;

    public function __construct(EntityManagerInterface $entityManager, ProfileRepository $profileRepository)
    {
        $this->entityManager = $entityManager;
        $this->profileRepository = $profileRepository;
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();

        $profile = $this->profileRepository->find($user->getUsername());
        if ($profile !== null) {
            $profile->setHidden(false);

            $this->entityManager->persist($profile);
            $this->entityManager->flush();
        }
    }

    public static function getSubscribedEvents()
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => ['onInteractiveLogin']
        ];
    }
}