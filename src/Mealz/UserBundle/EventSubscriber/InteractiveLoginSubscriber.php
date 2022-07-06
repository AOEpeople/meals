<?php

declare(strict_types=1);

namespace App\Mealz\UserBundle\EventSubscriber;

use App\Mealz\UserBundle\Repository\ProfileRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Http\SecurityEvents;

class InteractiveLoginSubscriber implements EventSubscriberInterface
{
    private EntityManagerInterface $entityManager;

    private ProfileRepositoryInterface $profileRepository;

    public function __construct(EntityManagerInterface $entityManager, ProfileRepositoryInterface $profileRepository)
    {
        $this->entityManager = $entityManager;
        $this->profileRepository = $profileRepository;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            SecurityEvents::INTERACTIVE_LOGIN => ['onInteractiveLogin'],
        ];
    }

    public function onInteractiveLogin(InteractiveLoginEvent $event): void
    {
        $user = $event->getAuthenticationToken()->getUser();

        $profile = $this->profileRepository->find($user->getUsername());
        if (null !== $profile) {
            $profile->setHidden(false);

            $this->entityManager->persist($profile);
            $this->entityManager->flush();
        }
    }
}
