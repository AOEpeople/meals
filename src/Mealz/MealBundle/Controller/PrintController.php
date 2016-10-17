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

        $participantRepository = $this->getParticipantRepository();
        $transactionRepository = $this->getDoctrine()->getRepository('MealzAccountingBundle:Transaction');
        $transactionsPerUser = $transactionRepository->findTotalAmountOfTransactionsPerUser();

        $users = $participantRepository->findCostsGroupedByUserGroupedByMonth();

        // create column names
        $numberOfMonths = 3;
        $columnNames = array('earlier' => 'Prior to that');
        $dateTime = new \DateTime("first day of -$numberOfMonths month 00:00");
        $earlierTimestamp = $dateTime->getTimestamp();
        for ($i = 0; $i < $numberOfMonths + 1; $i++) {
            $columnNames[$dateTime->getTimestamp()] = $dateTime->format('F');
            $dateTime->modify("+1 month");
        }
        $columnNames['total'] = 'Total';

        // create table rows
        foreach ($users as $username => &$user) {
            $userCosts = array_fill_keys(array_keys($columnNames), '0');
            foreach ($user['costs'] as $cost) {
                $monthCosts = $this->getRemainingCosts($cost['costs'], $transactionsPerUser[$username]);
                if ($cost['timestamp'] < $earlierTimestamp) {
                    $userCosts['earlier'] = bcadd($userCosts['earlier'], $monthCosts, 4);
                } else {
                    $userCosts[$cost['timestamp']] = $monthCosts;
                }
                $userCosts['total'] = bcadd($userCosts['total'], $monthCosts, 4);
            }
            if ($transactionsPerUser[$username] > 0) {
                $userCosts['total'] = '+'.$transactionsPerUser[$username];
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
        $week = $weekRepository->findWeekByDate($week->getStartTime(), TRUE);

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

    private function getRemainingCosts($costs, &$transactions)
    {
        $result = bcsub($costs, $transactions, 4);
        $transactions = abs($result);
        if ($result < 0) {
            $transactions = abs($result);
        } else {
            $transactions = 0;
        }

        return ($result < 0) ? 0 : $result * -1;
    }
}