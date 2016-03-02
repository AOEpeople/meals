<?php

namespace Mealz\MealBundle\Controller;

use Mealz\MealBundle\Entity\Participant;

class ParticipationConfirmationController extends BaseController
{
    public function indexAction()
    {
        $availableLetters = $this->getParticipantRepository()->getAvailableLetters();

        return $this->render('MealzMealBundle:Participation:index.html.twig', array(
            'availableLetters' => $availableLetters
        ));
    }

    public function indexByLetterAction($letter)
    {
        $participations = $this->getParticipantRepository()->getParticipantsTodayByLetter($letter);

        return $this->render('MealzMealBundle:Participation:indexByLetter.html.twig', array(
            'letter' => $letter,
            'participations' => $participations
        ));
    }

    public function confirmParticipationAction(Participant $participant)
    {
        $participant->setConfirmed(true);

        $em = $this->getDoctrine()->getManager();
        $em->persist($participant);
        $em->flush();

        return $this->redirectToRoute('MealzMealBundle_Participation_confirm_index');
    }
}