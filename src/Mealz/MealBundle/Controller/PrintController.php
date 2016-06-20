<?php

namespace Mealz\MealBundle\Controller;

class PrintController extends BaseController
{
    public function costSheetAction()
    {
        $participantRepository = $this->getParticipantRepository();

        $users = $participantRepository->findCostsGroupedByUserGroupedByMonth();

        $numberOfMonths = 3;
        $columnNames = array('earlier');
        $dateTime = new \DateTime("first day of -$numberOfMonths month 00:00");
        $earlierTimestamp = $dateTime->getTimestamp();
        for ($i = 0; $i < $numberOfMonths + 1; $i++) {
            array_push($columnNames, $dateTime->format('F'));
            $dateTime->modify("+1 month");
        }
        array_push($columnNames, 'total');

        foreach ($users as &$user) {
            $userCosts = array_fill_keys(array_values($columnNames), '0');
            foreach ($user as $cost) {
                $monthCosts = $cost['costs'];
                if ($cost['timestamp'] < $earlierTimestamp) {
                    $userCosts['earlier'] = bcadd($userCosts['earlier'], $monthCosts, 4);
                } else {
                    $monthName = date('F', $cost['timestamp']);
                    $userCosts[$monthName] = $monthCosts;
                }
                $userCosts['total'] = bcadd($userCosts['total'], $monthCosts, 4);
            }
            $user = $userCosts;
        }

        return $this->render('MealzMealBundle:Print:costSheet.html.twig', array(
            'columnNames' => $columnNames,
            'users' => $users
        ));
    }

}