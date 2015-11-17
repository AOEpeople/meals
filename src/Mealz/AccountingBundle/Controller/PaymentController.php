<?php

namespace Mealz\AccountingBundle\Controller;

use Mealz\AccountingBundle\Entity\Transaction;
use Mealz\AccountingBundle\Entity\TransactionRepository;
use Mealz\AccountingBundle\Service\Wallet;
use Mealz\MealBundle\Controller\BaseController;
use PayPal\Api\Amount;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction as PayPalTransaction;
use PayPal\Exception\PayPalConnectionException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PaymentController extends BaseController
{
    public function createPaymentAction()
    {
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            $profile = $this->getProfile();
        } else {
            throw new AccessDeniedException();
        }

        $apiContext = $this->getApiContext();

        /** @var Wallet $wallet */
        $wallet = $this->get('mealz_accounting.wallet');

        $walletAmount = $wallet->getBalance($profile);
        if ($walletAmount >= 0) {
            // add info message
            $this->addFlash(
                'error',
                "You don't have to balance anything!"
            );
            $this->redirectToRoute('MealzAccountingBundle_Accounting');
        }

        $walletAmount = abs($walletAmount);

        // create paypal payment
        $name = $profile->getName();

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $amount = new Amount();
        $amount->setCurrency('EUR');
        $amount->setTotal($walletAmount);

        $paypalTransaction = new PayPalTransaction();
        $paypalTransaction->setAmount($amount);
        $paypalTransaction->setDescription('Tasty meals.');

        $returnUrl = $this->generateUrl('mealz_accounting_payment_execute', array(), true);
        $cancelUrl = $this->generateUrl('MealzAccountingBundle_Accounting', array(), true);
        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl($returnUrl);
        $redirectUrls->setCancelUrl($cancelUrl);

        $payment = new Payment();
        $payment->setIntent('sale');
        $payment->setPayer($payer);
        $payment->setTransactions(array($paypalTransaction));
        $payment->setRedirectUrls($redirectUrls);

        $response = $payment->create($apiContext);

        // persist transaction in database
        $transaction = new Transaction();
        $transaction->setId($response->getId());
        $transaction->setUser($profile);
        $transaction->setAmount($walletAmount);

        $em = $this->getDoctrine()->getManager();
        $em->persist($transaction);
        $em->flush();

        // redirect to PayPal, append useraction=commit to show amount in PayPal checkout
        return $this->redirect($response->getApprovalLink()."&useraction=commit");
    }

    public function executePaymentAction(Request $request)
    {
        $apiContext = $this->getApiContext();

        $paymentId = $request->query->get('paymentId');

        // check if payment id is valid and was created by createPaymentAction
        /** @var TransactionRepository $transactionRepository */
        $transactionRepository = $this->get('mealz_accounting.repository.transaction');

        /** @var Transaction $transaction */
        $transaction = $transactionRepository->findOneBy(array(
            'id' => $paymentId,
            'successful' => 0
        ));

        if ($transaction === null) {
            throw new InvalidParameterException("Payment id isn't valid or transaction is already completed");
        }

        // get payment from PayPal and execute it
        $payment = Payment::get($paymentId, $apiContext);

        $payerInfo = $payment->getPayer()->getPayerInfo();
        $payerId = $payerInfo->getPayerId();

        $paymentExecution = new PaymentExecution();
        $paymentExecution->setPayerId($payerId);

        $payment->execute($paymentExecution, $apiContext);

        // mark transaction as successful in database
        $transaction->setSuccessful();
        $em = $this->getDoctrine()->getManager();
        $em->persist($transaction);
        $em->flush();

        // add info message
        $this->addFlash(
            'notice',
            'Your payment was successful!'
        );

        return $this->redirectToRoute('MealzAccountingBundle_Accounting');
    }

    private function getApiContext()
    {
        return new \PayPal\Rest\ApiContext(
            new \PayPal\Auth\OAuthTokenCredential(
                $this->getParameter('paypal.id'),
                $this->getParameter('paypal.secret')
            )
        );
    }
}