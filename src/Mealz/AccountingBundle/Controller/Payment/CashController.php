<?php

namespace App\Mealz\AccountingBundle\Controller\Payment;

use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\AccountingBundle\Form\CashPaymentAdminForm;
use App\Mealz\AccountingBundle\Service\Wallet;
use App\Mealz\MealBundle\Controller\BaseController;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class CashController.
 */
class CashController extends BaseController
{
    /**
     * @param Profile $profile
     */
    public function getPaymentFormForProfile($profile, Wallet $wallet): Response
    {
        $this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

        /** @var EntityManager $entityManager */
        $profileRepository = $this->getDoctrine()->getRepository(Profile::class);

        $profile = $profileRepository->find($profile);
        $action = $this->generateUrl('mealz_accounting_payment_cash_form_submit');
        $profileBalance = $wallet->getBalance($profile);

        $form = $this->createForm(
            CashPaymentAdminForm::class,
            new Transaction(),
            [
                'action' => $action,
                'profile' => $profile,
            ]
        );

        $template = 'MealzAccountingBundle:Accounting/Payment/Cash:form_cash_amount.html.twig';
        $renderedForm = $this->render(
            $template,
            [
                'form' => $form->createView(),
                'profileBalance' => $profileBalance,
            ]
        );

        return new Response($renderedForm->getContent());
    }

    /**
     * Renders the settlement overlay.
     *
     * @param Profile $profile
     */
    public function getSettlementFormForProfile($profile): Response
    {
        $this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

        $profileRepository = $this->getDoctrine()->getRepository(Profile::class);
        $profile = $profileRepository->find($profile);

        $template = 'MealzAccountingBundle:Accounting/Payment/Cash:form_cash_settlement.html.twig';
        $renderedForm = $this->render(
            $template,
            [
                'profile' => $profile,
            ]
        );

        return new Response($renderedForm->getContent());
    }

    /**
     * @Security("is_granted('ROLE_KITCHEN_STAFF')")
     */
    public function paymentFormHandling(Request $request): RedirectResponse
    {
        $transaction = new Transaction();
        $form = $this->createForm(CashPaymentAdminForm::class, $transaction);

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
                        [
                            '%amount%' => number_format(
                                $transaction->getAmount(), 2,
                                $this->get('translator')->trans('payment.separator.decimals'),
                                $this->get('translator')->trans('payment.separator.thousands')
                            ),
                            '%name%' => $transaction->getProfile()->getFullName(),
                        ],
                        'messages'
                    );
                    $this->addFlashMessage($message, 'success');

                    $logger = $this->get('monolog.logger.balance');
                    $logger->info('admin added {amount}€ into wallet of {profile} (Transaction: {transactionId})', [
                        'profile' => $transaction->getProfile(),
                        'amount' => $transaction->getAmount(),
                        'transactionId' => $transaction->getId(),
                    ]);
                } else {
                    $message = $this->get('translator')->trans('payment.cash.failure', [], 'messages');
                    $this->addFlashMessage($message, 'danger');
                }
            }
        }

        return $this->redirectToRoute('mealz_accounting.cost_sheet');
    }

    /**
     * Show transactions for logged in user.
     */
    public function showTransactionHistory(): Response
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        $profile = $this->getUser()->getProfile();

        $dateFrom = new DateTime('-28 days 00:00:00');
        $dateTo = new DateTime();

        list($transactionsTotal, $transactionHistory, $participationsTotal) = $this->getFullTransactionHistory(
            $dateFrom,
            $dateTo,
            $profile
        );

        ksort($transactionHistory);

        return $this->render(
            'MealzAccountingBundle:Accounting/User:transaction_history.html.twig',
            [
                'transaction_history_records' => $transactionHistory,
                'transactions_total' => $transactionsTotal,
                'participations_total' => $participationsTotal,
            ]
        );
    }

    /**
     * Merge participation and transactions into 1 array.
     */
    private function getFullTransactionHistory(DateTime $dateFrom, DateTime $dateTo, Profile $profile): array
    {
        $participantRepo = $this->getParticipantRepository();
        $participations = $participantRepo->getParticipantsOnDays($dateFrom, $dateTo, $profile);

        $transactionRepo = $this->getTransactionRepository();
        $transactions = $transactionRepo->getSuccessfulTransactionsOnDays($dateFrom, $dateTo, $profile);

        $transactionsTotal = 0;
        $transactionHistory = [];
        foreach ($transactions as $transaction) {
            $transactionsTotal += $transaction->getAmount();
            $transactionHistory[$transaction->getDate()->getTimestamp()] = $transaction;
        }

        $participationsTotal = 0;
        foreach ($participations as $participation) {
            $participationsTotal += $participation->getMeal()->getPrice();
            $transactionHistory[$participation->getMeal()->getDateTime()->getTimestamp() . '-' . $participation->getMeal()->getId()] = $participation;
        }

        return [$transactionsTotal, $transactionHistory, $participationsTotal];
    }
}
