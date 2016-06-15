<?php

namespace Mealz\MealBundle\Controller;

class PrintController extends BaseController
{
    public function costSheetAction()
    {
        $participantRepository = $this->getParticipantRepository();

        $costs = $participantRepository->findCostsGroupedByUserGroupedByMonth();

        return $this->render('MealzMealBundle:Print:costSheet.html.twig', array(
            'costs' => $costs
        ));
    }
}