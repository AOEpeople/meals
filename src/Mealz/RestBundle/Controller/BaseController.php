<?php

namespace App\Mealz\RestBundle\Controller;

use Doctrine\Persistence\ObjectManager;
use FOS\RestBundle\Controller\AbstractFOSRestController;
use App\Mealz\MealBundle\Entity\MealRepository;
use App\Mealz\MealBundle\Entity\ParticipantRepository;
use App\Mealz\MealBundle\Entity\WeekRepository;
use App\Mealz\MealBundle\Service\Doorman;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BaseController extends AbstractFOSRestController
{
    public function getSecurityContext()
    {
        return $this->get('security.context');
    }

    public function getTokenStorage()
    {
        return $this->container->get('security.token_storage');
    }

    public function checkUser()
    {
        if (null === $this->getUser()) {
            throw new HttpException(401, "The access token provided is invalid.");
        }
    }

    public function getUser()
    {
        $token = $this->getTokenStorage()->getToken();
        if (null === $token) {
            return null;
        }
        $user = $token->getUser();
        if (!is_object($user)) {
            return null;
        }
        return $user;
    }

    /**
     * @return ObjectManager
     */
    public function getManager()
    {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @return Doorman
     */
    public function getDoorman()
    {
        return $this->get('mealz_meal.doorman');
    }

    /**
     * @return ParticipantRepository
     */
    public function getParticipantRepository()
    {
        return $this->getDoctrine()->getRepository('MealzMealBundle:Participant');
    }

    /**
     * @return WeekRepository
     */
    public function getWeekRepository()
    {
        return $this->getDoctrine()->getRepository('MealzMealBundle:Week');
    }

    /**
     * @return MealRepository
     */
    public function getMealRepository()
    {
        return $this->getDoctrine()->getRepository('MealzMealBundle:Meal');
    }
}
