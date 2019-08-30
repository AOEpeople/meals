<?php

namespace Mealz\AccountingBundle\Controller;

use Mealz\AccountingBundle\Entity\Transaction;
use Mealz\AccountingBundle\Entity\TransactionRepository;
use Mealz\MealBundle\Controller\BaseController;
use Symfony\Component\Translation\Translator;
use Symfony\Component\VarDumper\VarDumper;

class CostSheetController extends BaseController
{
    /**
     * @TODO: use own data model for user costs
     */
    public function listAction()
    {
        $this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

        $participantRepository = $this->getParticipantRepository();
        $transactionRepository = $this->getDoctrine()->getRepository('MealzAccountingBundle:Transaction');
        $transactionsPerUser = $transactionRepository->findUserDataAndTransactionAmountForGivenPeriod();

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

        $message = $this->get('translator')->trans('payment.costsheet.confirmation.error');
        $severity = "danger";

        if ($profile !== null && $profile->getSettlementHash() === null) {
            $username = $profile->getUsername();
            $secret = $this->getParameter('secret');
            $hashCode = crypt($username, $secret);

            $em = $this->getDoctrine()->getManager();
            $profile->setSettlementHash($hashCode);
            $em->persist($profile);
            $em->flush();

            $urlEncodedHash = urlencode(crypt($username, $secret));

            $translator = $this->get('translator');

            $to = $username . $translator->trans('mail.domain', array(), 'messages');
            $subject = $translator->trans('payment.costsheet.mail.subject', array(), 'messages');
            $header = $translator->trans('mail.sender', array(), 'messages');
            $body = $translator->trans('payment.costsheet.mail.body', array(
                '%admin%' => $this->getProfile()->getFullName(),
                '%fullname%' => $profile->getFullName(),
                '%link%' => $_SERVER['SERVER_NAME'] . $this->generateUrl("mealz_accounting_cost_sheet_redirect_to_confirm", array(
                            "hash" => $urlEncodedHash)
                    )
            ), 'messages');

            VarDumper::dump($to . $subject . $header . $body);

            //mail("raza.ahmed@aoe.com", "Test Subject", $urlEncodedHash, "From: AOE Meals Chef Bot <noreply-meals@aoe.com>");

            $message = $this->get('translator')->trans(
                'payment.costsheet.confirmation.success',
                array(
                    '%name%' => $profile->getFullName(),
                ),
                'messages'
            );
            $severity = "success";
        }

        $this->addFlashMessage($message, $severity);
        return $this->listAction();

    }

    /**
     * @param $username
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function renderConfirmButtonAction($hash)
    {
        return $this->render('MealzAccountingBundle::confirmationPage.html.twig', array(
            'hash' => $hash)
        );
    }

    /**
     * @param $hash
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function confirmSettlementAction($hash)
    {
        $profileRepository = $this->getDoctrine()->getRepository('MealzUserBundle:Profile');
        $queryResult = $profileRepository->findBy(array("settlementHash" => urldecode($hash)));

        $message = "The user could not be found or the request has already been confirmed.";
        $severity = "danger";

        if ($queryResult != false) {
            $profile = $queryResult[0];
            $profile->setSettlementHash(null);

            $transaction = new Transaction();
            $transaction->setProfile($profile);
            $transaction->setDate(new \DateTime());
            $transaction->setAmount(abs($this->get('mealz_accounting.wallet')->getBalance($profile)));

            $em = $this->getDoctrine()->getManager();
            $em->persist($profile);
            $em->persist($transaction);
            $em->flush();

            $message = "The account settlement for " . $profile->getFullName() . " was confirmed.";
            $severity = "success";
        }

        $this->addFlashMessage($message, $severity);
        return $this->render("::base.html.twig");
    }

}
