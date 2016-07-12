<?php

namespace Mealz\MealBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Mealz\MealBundle\Entity\Week;
use Mealz\MealBundle\Entity\WeekRepository;

class PrintController extends BaseController
{
    /**
     * @TODO: use own data model for user costs
     */
    public function costSheetAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        /** @var Translator $translator */
        $translator = $this->get('translator');

        $participantRepository = $this->getParticipantRepository();

        $users = $participantRepository->findCostsGroupedByUserGroupedByMonth();

        // create column names
        $numberOfMonths = 3;
        $columnNames = array('earlier' => $translator->trans('costs.earlier', array(), 'general'));
        $dateTime = new \DateTime("first day of -$numberOfMonths month 00:00");
        $earlierTimestamp = $dateTime->getTimestamp();
        for ($i = 0; $i < $numberOfMonths + 1; $i++) {
            $columnNames[$dateTime->getTimestamp()] = $dateTime->format('F');
            $dateTime->modify("+1 month");
        }
        $columnNames['total'] = $translator->trans('costs.total', array(), 'general');

        // create table rows
        foreach ($users as &$user) {
            $userCosts = array_fill_keys(array_keys($columnNames), '0');
            foreach ($user['costs'] as $cost) {
                $monthCosts = $cost['costs'];
                if ($cost['timestamp'] < $earlierTimestamp) {
                    $userCosts['earlier'] = bcadd($userCosts['earlier'], $monthCosts, 4);
                } else {
                    $userCosts[$cost['timestamp']] = $monthCosts;
                }
                $userCosts['total'] = bcadd($userCosts['total'], $monthCosts, 4);
            }
            $user['costs'] = $userCosts;
        }

        return $this->render('MealzMealBundle:Print:costSheet.html.twig', array(
            'columnNames' => $columnNames,
            'users' => $users
        ));
    }

    public function participationsAction(Week $week)
    {
        if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        /** @var WeekRepository $weekRepository */
        $weekRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Week');
        $week = $weekRepository->findWeekByDate($week->getStartTime());

        $participantRepository = $this->getParticipantRepository();
        $participations = $participantRepository->getParticipantsOnDays(
            $week->getStartTime(),
            $week->getEndTime()
        );

        /**
         * @TODO: get participants through week entity
         */
        $groupedParticipations = $participantRepository->groupParticipantsByName($participations);

        return $this->render('MealzMealBundle:Print:participations.html.twig', array(
            'week' => $week,
            'users' => $groupedParticipations
        ));
    }
}