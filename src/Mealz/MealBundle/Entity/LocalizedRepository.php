<?php

namespace Mealz\MealBundle\Entity;

use Doctrine\ORM\EntityRepository;
use Mealz\MealBundle\EventListener\LocalisationListener;

abstract class LocalizedRepository extends EntityRepository
{
    /** @var  LocalisationListener */
    protected $localizationListener;

    /**
     * @param LocalisationListener $localisationListener
     */
    public function setLocalizationListener(LocalisationListener $localisationListener)
    {
        $this->localizationListener = $localisationListener;
    }
}