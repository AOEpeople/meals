<?php

namespace App\Mealz\MealBundle\Service;

use App\Mealz\MealBundle\Entity\LocalizedRepository;
use App\Mealz\MealBundle\EventListener\LocalisationListener;
use Doctrine\ORM\EntityManagerInterface;

class LocalizedRepositoryFactory
{
    protected EntityManagerInterface $entityManager;

    protected LocalisationListener $localisationListener;

    public function __construct(EntityManagerInterface $entityManager, LocalisationListener $localisationListener)
    {
        $this->entityManager = $entityManager;
        $this->localisationListener = $localisationListener;
    }

    public function createLocalizedRepository($repositoryName): LocalizedRepository
    {
        /** @var LocalizedRepository $repository */
        $repository = $this->entityManager->getRepository($repositoryName);
        $repository->setLocalizationListener($this->localisationListener);

        return $repository;
    }
}
