<?php

namespace Mealz\AccountingBundle\Controller\Payment;

use Doctrine\ORM\EntityManager;
use Mealz\MealBundle\Controller\BaseController;
use Mealz\AccountingBundle\Entity\Transaction;
use Mealz\MealBundle\Entity\Participant;
use Mealz\UserBundle\Entity\Profile;
use Symfony\Component\HttpFoundation\Request;
use Mealz\AccountingBundle\Form\CashPaymentAdminForm;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;

class CashController extends BaseController
{

    public function getPaymentFormForProfileAction($profile)
    {
        if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $profileRepository = $this->getDoctrine()->getRepository('MealzUserBundle:Profile');

        $profile = $profileRepository->find($profile);
        $action = $this->generateUrl('mealz_accounting_payment_cash_form_submit');

        $form = $this->createForm(
            new CashPaymentAdminForm($em),
            new Transaction(),
            array(
                'action' => $action,
                'profile' => $profile,
            )
        );

        $template = "MealzAccountingBundle:Accounting/Payment/Cash:form_cash_amount.html.twig";
        $renderedForm = $this->render($template, array('form' => $form->createView()));

        return new JsonResponse($renderedForm->getContent());
    }

    public function paymentFormHandlingAction(Request $request)
    {
        if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $transaction = new Transaction();
        $form = $this->createForm(new CashPaymentAdminForm($em), $transaction);

        // handle form submission
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                if ($transaction->getAmount() > 0) {
                    $em = $this->getDoctrine()->getManager();
                    $em->persist($transaction);
                    $em->flush();

                    $message = $this->get('translator')->trans(
                        'payment.cash.success',
                        array(
                            '%amount%' => $transaction->getAmount(),
                            '%name%' => $transaction->getProfile()->getFullName()
                        ),
                        'messages'
                    );
                    $this->addFlashMessage($message, 'success');

                    $logger = $this->get('monolog.logger.balance');
                    $logger->addInfo('admin added {amount}€ into wallet of {profile} (Transaction: {transactionId})', array(
                        "profile" => $transaction->getProfile(),
                        "amount" => $transaction->getAmount(),
                        "transactionId" => $transaction->getId()
                    ));
                } else {
                    $message = $this->get('translator')->trans('payment.cash.failure',array(),'messages');
                    $this->addFlashMessage($message, 'danger');
                }
            }
        }

        $weekRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Week');
        $week = $weekRepository->getCurrentWeek();

        return $this->redirectToRoute('MealzMealBundle_Print_costSheet', array(
            'week' => $week->getId()
        ));
    }

    /**
     * Show transactions for logged in user
     *
     * @param Request $request request
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showTransactionHistoryAction(Request $request)
    {
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        $profile = $this->getUser()->getProfile();

        $dateFrom = new \DateTime();
        $dateFrom->modify('-4 weeks');
        $dateTo = new \DateTime();

        $participantRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Participant');
        $participations = $participantRepository->getParticipantsOnDays($dateFrom, $dateTo, $profile);

        $transactionRepository = $this->getDoctrine()->getRepository('MealzAccountingBundle:Transaction');
        $transactions = $transactionRepository->getSuccessfulTransactionsOnDays($dateFrom, $dateTo, $profile);

        $transactionsTotal = 0;
        $transactionHistoryArr = array();
        foreach ($transactions as $transaction) {
            $transactionsTotal += $transaction->getAmount();
            $transactionHistoryArr[$transaction->getDate()->getTimestamp()] = $transaction;
        }

        $participationsTotal = 0;
        /** @var $participation Participant */
        foreach ($participations as $participation) {
            $participationsTotal += $participation->getMeal()->getPrice();
            $transactionHistoryArr[$participation->getMeal()->getDateTime()->getTimestamp()] = $participation;
        }

        ksort($transactionHistoryArr);

        return $this->render(
            'MealzAccountingBundle:Accounting\\User:transaction_history.html.twig',
            array(
                'transaction_history_records' => $transactionHistoryArr,
                'transactions_total' => $transactionsTotal,
                'participations_total' => $participationsTotal
            )
        );
    }
}
