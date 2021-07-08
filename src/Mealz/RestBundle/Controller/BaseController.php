<?php
/**
 * Created by PhpStorm.
 * User: marko.milojevic
 * Date: 26/07/16
 * Time: 18:06
 */

namespace Mealz\RestBundle\Controller;

use Doctrine\Common\Persistence\ObjectManager;
use FOS\RestBundle\Controller\FOSRestController;
use Mealz\MealBundle\Entity\MealRepository;
use Mealz\MealBundle\Entity\ParticipantRepository;
use Mealz\MealBundle\Entity\WeekRepository;
use Mealz\MealBundle\Service\Doorman;
use Symfony\Component\HttpKernel\Exception\HttpException;

class BaseController extends FOSRestController
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
