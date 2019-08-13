<?php

namespace Mealz\AccountingBundle\Controller\Payment;

use Doctrine\ORM\EntityManager;
use Mealz\AccountingBundle\Service\Wallet;
use Mealz\MealBundle\Controller\BaseController;
use Mealz\AccountingBundle\Entity\Transaction;
use Mealz\MealBundle\Entity\Participant;
use Mealz\UserBundle\Entity\Profile;
use Symfony\Component\HttpFoundation\Request;
use Mealz\AccountingBundle\Form\EcashPaymentAdminForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class EcashController
 * @package Mealz\AccountingBundle\Controller\Payment
 */
class EcashController extends BaseController
{
    /**
     * @param Profile $profile
     * @return JsonResponse
     */
    public function getPaymentFormForProfileAction($profile)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $profileRepository = $this->getDoctrine()->getRepository('MealzUserBundle:Profile');

        $profile = $profileRepository->find($profile);
        $action = $this->generateUrl('mealz_accounting_payment_ecash_form_submit');

        // Default value for Ecash payment overlay
        $balance = $this->getWallet()->getBalance($profile) * (-1);
        if ($balance <= 0) {
            $balance = 0;
        }

        $form = $this->createForm(
            new EcashPaymentAdminForm($em),
            new Transaction(),
            array(
                'action' => $action,
                'profile' => $profile,
                'balance' => $balance,
            )
        );

        $template = "MealzAccountingBundle:Accounting/Payment/Ecash:form_ecash_amount.html.twig";
        $renderedForm = $this->render($template, array('form' => $form->createView()));

        return new JsonResponse($renderedForm->getContent());
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function paymentFormHandlingAction(Request $request)
    {
        $formArray = [];
        if ($content = $request->getContent()) {
            // clean Form Array
            foreach (json_decode($content, true) as $formValue) {
                $formArray[$formValue['name']] = $formValue['value'];
            }
        }

        // Check if required fields are set and PayMethod is Paypal (paymethod=0)
        if ($request->isMethod('POST')
            && !empty($formArray['ecash[orderid]'])
            && !empty($formArray['ecash[profile]'])
            && ((float)$formArray['ecash[amount]'] > 0.00)
            && ($formArray['ecash[paymethod]'] === '0')
            && !empty($formArray['ecash[_token]'])
        ) {

            $id = $this->container->get('twig')->getGlobals()['paypal_id'];
            $secret = $this->container->get('twig')->getGlobals()['paypal_secret'];
            PaypalClient::setCredentials($id, $secret);

            // 3. Call PayPal to get the transaction details
            $client = PaypalClient::client();

            $response = $client->execute(new OrdersGetRequest($formArray['ecash[orderid]']));

            if ($response->statusCode == 200) {

                /** @var EntityManager $em */
                $em = $this->getDoctrine()->getManager();
                $transaction = new Transaction();

                $em = $this->getDoctrine()->getManager();
                $profileRepository = $this->getDoctrine()->getRepository('MealzUserBundle:Profile');
                $profile = $profileRepository->find($formArray['ecash[profile]']);

                $transaction->setProfile($profile);
                $transaction->setOrderid($formArray['ecash[amount]']);
                $transaction->setAmount($formArray['ecash[amount]']);
                $transaction->setDate(new \DateTime());
                $transaction->setPaymethod($formArray['ecash[paymethod]']);

                $em->persist($transaction);
                $em->flush();

            }
        } else {
            $this->addFlashMessage("Payment failed!", 'error');
            return new Response(
                $this->generateUrl('mealz_accounting_payment_ecash_transaction_history'),
                Response::HTTP_NOT_FOUND,
                ['content-type' => 'text/html']
            );
        }

        $this->addFlashMessage("Payment successful!", 'success');
        return new Response(
            $this->generateUrl('mealz_accounting_payment_ecash_transaction_history'),
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function showTransactionHistoryAction(Request $request)
    {
        $profile = $this->getUser()->getProfile();

        $dateFrom = new \DateTime();
        $dateFrom->modify('-4 weeks');
        $dateTo = new \DateTime();

        list($transactionsTotal, $transactionHistoryArr, $participationsTotal) = $this->getFullTransactionHistory(
            $dateFrom,
            $dateTo,
            $profile
        );

        ksort($transactionHistoryArr);

        return $this->render(
            'MealzAccountingBundle:Accounting\\User:transaction_history.html.twig',
            array(
                'transaction_history_records' => $transactionHistoryArr,
                'transactions_total' => $transactionsTotal,
                'participations_total' => $participationsTotal,
            )
        );
    }

    /**
     * @return Wallet
     */
    private function getWallet()
    {
        return $this->get('mealz_accounting.wallet');
    }

    /**
     * Merge participation and transactions into 1 array
     *
     * @param \DateTime $dateFrom min date
     * @param \DateTime $dateTo max date
     * @param Profile $profile User profile
     *
     * @return array
     */
    public function getFullTransactionHistory($dateFrom, $dateTo, $profile)
    {
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

        return array($transactionsTotal, $transactionHistoryArr, $participationsTotal);
    }
}
