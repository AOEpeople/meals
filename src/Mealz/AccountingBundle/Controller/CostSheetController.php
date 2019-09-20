<?php

namespace Mealz\AccountingBundle\Controller;

use Mealz\AccountingBundle\Entity\Transaction;
use Mealz\MealBundle\Controller\BaseController;
use Mealz\UserBundle\Entity\Profile;

class CostSheetController extends BaseController
{
    /**
     * @TODO: use own data model for user costs
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

        $participantRepo = $this->getParticipantRepository();
        $transactionRepo = $this->getDoctrine()->getRepository('MealzAccountingBundle:Transaction');
        $transactionsPerUser = $transactionRepo->findUserDataAndTransactionAmountForGivenPeriod();

        $users = $participantRepo->findCostsGroupedByUserGroupedByMonth();

        // create column names
        $numberOfMonths = 3;
        $columnNames = array('earlier' => 'Prior to that');
        $dateTime = new \DateTime("first day of -$numberOfMonths month 00:00");
        $earlierTimestamp = $dateTime->getTimestamp();
        for ($i = 0; $i < $numberOfMonths + 1; $i++) {
            $columnNames[$dateTime->getTimestamp()] = $dateTime->format('F');
            $dateTime->modify('+1 month');
        }
        $columnNames['total'] = 'Total';

        // create table rows
        foreach ($users as $username => &$user) {
            $userCosts = array_fill_keys(array_keys($columnNames), '0');
            foreach ($user['costs'] as $cost) {
                $monthCosts = $this->getRemainingCosts($cost['costs'], $transactionsPerUser[$username]['amount']);
                if ($cost['timestamp'] < $earlierTimestamp) {
                    $userCosts['earlier'] = bcadd($userCosts['earlier'], $monthCosts, 4);
                } else {
                    $userCosts[$cost['timestamp']] = $monthCosts;
                }
                $userCosts['total'] = bcadd($userCosts['total'], $monthCosts, 4);
            }
            if ($transactionsPerUser[$username]['amount'] > 0) {
                $userCosts['total'] = '+' . $transactionsPerUser[$username]['amount'];
            }
            $user['costs'] = $userCosts;
        }

        return $this->render('MealzAccountingBundle::costSheet.html.twig', array(
            'columnNames' => $columnNames,
            'users' => $users
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

    /**
     * @param String $username
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function sendSettlementRequestAction($username)
    {
        $this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

        $profileRepository = $this->getDoctrine()->getRepository('MealzUserBundle:Profile');
        $profile = $profileRepository->find($username);

        if ($profile !== null && $profile->getSettlementHash() === null && $this->get('mealz_accounting.wallet')->getBalance($profile) > 0.00) {
            $username = $profile->getUsername();
            $secret = $this->getParameter('secret');
            $hashCode = str_replace('/', '', crypt($username, $secret));
            $urlEncodedHash = urlencode($hashCode);

            $entityManager = $this->getDoctrine()->getManager();
            $profile->setSettlementHash($hashCode);
            $entityManager->persist($profile);
            $entityManager->flush();

            $this->sendSettlementRequestMail($profile, $urlEncodedHash);

            $message = $this->get('translator')->trans(
                'payment.costsheet.account_settlement.request.success',
                array(
                    '%name%' => $profile->getFullName(),
                ),
                'messages'
            );
            $severity = 'success';
        } else {
            $message = $this->get('translator')->trans('payment.costsheet.account_settlement.request.failure');
            $severity = 'danger';
        }

        $this->addFlashMessage($message, $severity);
        return $this->listAction();
    }

    /**
     * @param String $hash
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderConfirmButtonAction($hash)
    {
        $profile = null;
        $profileRepository = $this->getDoctrine()->getRepository('MealzUserBundle:Profile');
        $queryResult = $profileRepository->findBy(array('settlementHash' => urldecode($hash)));

        if (is_array($queryResult) === true && empty($queryResult) === false) {
            $profile = $queryResult[0];
        } else {
            $this->addFlashMessage($this->get('translator')->trans('payment.costsheet.account_settlement.confirmation.failure'), 'danger');
        }

        return $this->render('MealzAccountingBundle::confirmationPage.html.twig', array(
            'hash' => $hash,
            'profile' => $profile));
    }

    /**
     * @param String $hash
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function confirmSettlementAction($hash)
    {
        $profileRepository = $this->getDoctrine()->getRepository('MealzUserBundle:Profile');
        $queryResult = $profileRepository->findBy(array('settlementHash' => urldecode($hash)));

        if (is_array($queryResult) === true && empty($queryResult) === false) {
            $profile = $queryResult[0];
            $profile->setSettlementHash(null);

            $transaction = new Transaction();
            $transaction->setProfile($profile);
            $transaction->setDate(new \DateTime());
            $transaction->setAmount(-1 * abs(floatval($this->get('mealz_accounting.wallet')->getBalance($profile))));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($profile);
            $entityManager->persist($transaction);
            $entityManager->flush();

            /**
             * for devbox situation, if you are not loged in with fake-login
             * With Keycloak this if condition is not needed anymore
             */
            if ($this->getProfile() !== null) {
                $logger = $this->get('monolog.logger.balance');
                $logger->addInfo(
                    '{hr_member} settled {users} Balance.',
                    [
                        'hr_member' => $this->getProfile()->getFullName(),
                        'users' => $profile->getFullName(),
                    ]
                );
            }

            $message =
                $this->get('translator')->trans('payment.costsheet.account_settlement.confirmation.success', array(
                    '%fullname%' => $profile->getFullName(),
                ));
            $severity = 'success';
        } else {
            $message =
                $this->get('translator')->trans('payment.costsheet.account_settlement.confirmation.failure');
            $severity = 'danger';
        }

        $this->addFlashMessage($message, $severity);
        return $this->render('@MealzAccounting/confirmationPage.html.twig', array(
            'profile' => null
        ));
    }

    /**
     * @param Profile $profile
     * @param String $urlEncodedHash
     */
    private function sendSettlementRequestMail(Profile $profile, $urlEncodedHash)
    {
        $translator = $this->get('translator');

        $receiver = $this->getParameter('hr_email');
        $subject = $translator->trans('payment.costsheet.mail.subject', array(), 'messages');
        $body = $translator->trans('payment.costsheet.mail.body', array(
            '%admin%' => $this->getProfile()->getFullName(),
            '%fullname%' => $profile->getFullName(),
            '%link%' => $this->getParameter('env_url') . $this->generateUrl('mealz_accounting_cost_sheet_redirect_to_confirm', array(
                    'hash' => $urlEncodedHash))
        ), 'messages');
        $headers = array();
        $headers[] = $translator->trans('mail.sender', array(), 'messages');
        $headers[] = "Content-type: text/plain; charset=utf-8";

        mail($receiver, $subject, $body, implode("\r\n", $headers));
    }
}
