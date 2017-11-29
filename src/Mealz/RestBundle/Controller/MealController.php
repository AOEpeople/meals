<?php

namespace Mealz\RestBundle\Controller;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\ORM\EntityManager;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\EventListener\ParticipantNotUniqueException;

class MealController extends BaseController {

    public function addAction($date, $dishId) {
        $this->checkUser();

        if(null == $date) {
            throw new HttpException(400, "Date is missing.");
        }
        if(null == $dishId) {
            throw new HttpException(400, "Dish's id is missing.");
        }

        /** @var \Mealz\MealBundle\Entity\Meal $meal */
        $meal = $this->getMealRepository()->findOneByDateAndDish($date, $dishId);

        if(!$meal) {
            throw new HttpException(404, "There is no such meal.");
        }
        if(!$this->getDoorman()->isUserAllowedToJoin($meal)) {
            throw new HttpException(403, "It's not allowed for participant to join.");
        }

        /** @var \Mealz\UserBundle\Entity\Profile $profile */
        $profile = $this->getUser()->getProfile();

        try {
            $participant = new Participant();
            $participant->setProfile($profile);
            $participant->setMeal($meal);

            $manager = $this->getDoctrine()->getManager();
            $manager->transactional(function (EntityManager $manager) use ($participant) {
                $manager->persist($participant);
                $manager->flush();
            });
        } catch (ParticipantNotUniqueException $e) {
            throw new HttpException(422, "Participant must be unique.");
        }

        return array(
            'participantsCount' => $participant->getMeal()->getParticipants()->count(),
            'participantId' => $participant->getId()
        );
    }
}
