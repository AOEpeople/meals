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

/**
 * Class CashController
 * @package Mealz\AccountingBundle\Controller\Payment
 */
class CashController extends BaseController
{
    /**
     * @param Profile $profile
     * @return JsonResponse
     */
    public function getPaymentFormForProfileAction($profile)
    {
        if ($this->get('security.helper')->isGranted('ROLE_KITCHEN_STAFF') === false) {
            throw new AccessDeniedException();
        }

        /** @var EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $profileRepository = $this->getDoctrine()->getRepository('MealzUserBundle:Profile');

        $profile = $profileRepository->find($profile);
        $action = $this->generateUrl('mealz_accounting_payment_cash_form_submit');
        $profileBalance = $this->get('mealz_accounting.wallet')->getBalance($profile);

        $form = $this->createForm(
            \Mealz\AccountingBundle\Form\CashPaymentAdminForm::class,
            new Transaction(),
            array(
                'action' => $action,
                'profile' => $profile,
            )
        );

        $template = "MealzAccountingBundle:Accounting/Payment/Cash:form_cash_amount.html.twig";
        $renderedForm = $this->render(
            $template,
            array(
                'form' => $form->createView(),
                'profileBalance' => $profileBalance
            )
        );

        return new JsonResponse($renderedForm->getContent());
    }

    /**
     * Renders the settlement overlay
     * @param Profile $profile
     * @return JsonResponse
     */
    public function getSettlementFormForProfileAction($profile)
    {
        if ($this->get('security.helper')->isGranted('ROLE_KITCHEN_STAFF') === false) {
            throw new AccessDeniedException();
        }

        $profileRepository = $this->getDoctrine()->getRepository('MealzUserBundle:Profile');

        $profile = $profileRepository->find($profile);

        $template = "MealzAccountingBundle:Accounting/Payment/Cash:form_cash_settlement.html.twig";
        $renderedForm = $this->render(
            $template,
            array(
                'profile' => $profile
            )
        );

        return new JsonResponse($renderedForm->getContent());
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function paymentFormHandlingAction(Request $request)
    {
        if ($this->get('security.helper')->isGranted('ROLE_KITCHEN_STAFF') === false) {
            throw new AccessDeniedException();
        }

        /** @var EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $transaction = new Transaction();
        $form = $this->createForm(\Mealz\AccountingBundle\Form\CashPaymentAdminForm::class, $transaction, ['entityManager' => $entityManager]);

        // handle form submission
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                if ($transaction->getAmount() > 0) {
                    $entityManager = $this->getDoctrine()->getManager();
                    $entityManager->persist($transaction);
                    $entityManager->flush();

                    $message = $this->get('translator')->trans(
                        'payment.cash.success',
                        array(
                            '%amount%' => $transaction->getAmount(),
                            '%name%' => $transaction->getProfile()->getFullName(),
                        ),
                        'messages'
                    );
                    $this->addFlashMessage($message, 'success');

                    $logger = $this->get('monolog.logger.balance');
                    $logger->addInfo('admin added {amount}€ into wallet of {profile} (Transaction: {transactionId})', array(
                        "profile" => $transaction->getProfile(),
                        "amount" => $transaction->getAmount(),
                        "transactionId" => $transaction->getId(),
                    ));
                } else {
                    $message = $this->get('translator')->trans('payment.cash.failure', array(), 'messages');
                    $this->addFlashMessage($message, 'danger');
                }
            }
        }

        return $this->redirectToRoute('mealz_accounting.cost_sheet');
    }

    /**
     * Show transactions for logged in user
     * Used in routing as an Action
     *
     * @param Request $request request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function showTransactionHistoryAction()
    {
        if (false === $this->get('security.authorization_checker')->isGranted('ROLE_USER')) {
            throw new AccessDeniedException();
        }

        $profile = $this->getUser()->getProfile();

        $dateFrom = new \DateTime();
        $dateFrom->modify('-28 days');
        $dateTo = new \DateTime();

        list($transactionsTotal, $transactionHistory, $participationsTotal) = $this->getFullTransactionHistory(
            $dateFrom,
            $dateTo,
            $profile
        );

        ksort($transactionHistory);

        return $this->render(
            'MealzAccountingBundle:Accounting\\User:transaction_history.html.twig',
            array(
                'transaction_history_records' => $transactionHistory,
                'transactions_total' => $transactionsTotal,
                'participations_total' => $participationsTotal,
            )
        );
    }

    /**
     * Merge participation and transactions into 1 array
     *
     * @param \DateTime $dateFrom min date
     * @param \DateTime $dateTo   max date
     * @param Profile   $profile  User profile
     *
     * @return array
     */
    public function getFullTransactionHistory($dateFrom, $dateTo, $profile)
    {
        $participantRepo = $this->getDoctrine()->getRepository('MealzMealBundle:Participant');
        $participations = $participantRepo->getParticipantsOnDays($dateFrom, $dateTo, $profile);

        $transactionRepo = $this->getDoctrine()->getRepository('MealzAccountingBundle:Transaction');
        $transactions = $transactionRepo->getSuccessfulTransactionsOnDays($dateFrom, $dateTo, $profile);

        $transactionsTotal = 0;
        $transactionHistory = array();
        foreach ($transactions as $transaction) {
            $transactionsTotal += $transaction->getAmount();
            $transactionHistory[$transaction->getDate()->getTimestamp()] = $transaction;
        }

        $participationsTotal = 0;
        /** @var $participation Participant */
        foreach ($participations as $participation) {
            $participationsTotal += $participation->getMeal()->getPrice();
            $transactionHistory[$participation->getMeal()->getDateTime()->getTimestamp() .'-'. $participation->getMeal()->getId()] = $participation;
        }

        return array($transactionsTotal, $transactionHistory, $participationsTotal);
    }
}
