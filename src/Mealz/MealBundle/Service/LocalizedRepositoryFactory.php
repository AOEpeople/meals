<?php

namespace Mealz\MealBundle\Service;

use Doctrine\ORM\EntityManager;
use Mealz\MealBundle\Entity\LocalizedRepository;
use Mealz\MealBundle\EventListener\LocalisationListener;

class LocalizedRepositoryFactory
{
    /** @var EntityManager $em */
    protected $em;

    /** @var LocalisationListener $localisationListener */
    protected $localisationListener;

    public function __construct(EntityManager $em, LocalisationListener $localisationListener)
    {
        $this->em = $em;
        $this->localisationListener = $localisationListener;
    }

    public function createLocalizedRepository($repositoryName)
    {
        /** @var LocalizedRepository $repository */
        $repository = $this->em->getRepository($repositoryName);
        $repository->setLocalizationListener($this->localisationListener);
        return $repository;
    }
}