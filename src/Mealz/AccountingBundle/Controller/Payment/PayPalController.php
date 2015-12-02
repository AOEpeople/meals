<?php

namespace Mealz\AccountingBundle\Controller\Payment;

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
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\InvalidParameterException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class PayPalController extends BaseController
{
    /** @var \Mealz\UserBundle\Entity\Profile $profile */
    private $profile;

    public function createCustomPaymentAction(Request $request)
    {
        $this->checkSecurityContext();
        $form = $this->generateVariabePaymentForm();

        // handle form submission
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $amount = $form->get('amount')->getData();
                return $this->createPaymentWithAmount($amount);
            }
        }

        return $this->render('MealzAccountingBundle:Accounting\\partials:form_payment_paypal_custom.html.twig', array(
            'paypalVariablePaymentForm' => $form->createView()
        ));
    }

    public function createBalancePaymentAction()
    {
        $this->checkSecurityContext();

        /** @var Wallet $wallet */
        $wallet = $this->get('mealz_accounting.wallet');

        $walletAmount = $wallet->getBalance($this->profile);
        if ($walletAmount >= 0) {
            // add info message
            $this->addFlash(
                'error',
                "You don't have to balance anything!"
            );
            return $this->redirectToRoute('MealzAccountingBundle_Wallet');
        }

        return $this->createPaymentWithAmount($walletAmount);
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

    private function createPaymentWithAmount($amount)
    {
        $apiContext = $this->getApiContext();

        $amount = abs($amount);

        // create paypal payment
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $paypalAmount = new Amount();
        $paypalAmount->setCurrency('EUR');
        $paypalAmount->setTotal($amount);

        $paypalTransaction = new PayPalTransaction();
        $paypalTransaction->setAmount($paypalAmount);
        $paypalTransaction->setDescription('Tasty meals.');

        $returnUrl = $this->generateUrl('mealz_accounting_payment_paypal_execute', array(), true);
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
        $transaction->setUser($this->profile);
        $transaction->setAmount($amount);

        $em = $this->getDoctrine()->getManager();
        $em->persist($transaction);
        $em->flush();

        // redirect to PayPal, append useraction=commit to show amount in PayPal checkout
        return $this->redirect($response->getApprovalLink() . "&useraction=commit");
    }

    private function checkSecurityContext()
    {
        if ($this->get('security.context')->isGranted('ROLE_USER')) {
            $this->profile = $this->getProfile();
        } else {
            throw new AccessDeniedException();
        }
    }

    private function getApiContext()
    {
        return new ApiContext(
            new OAuthTokenCredential(
                $this->getParameter('paypal.id'),
                $this->getParameter('paypal.secret')
            )
        );
    }

    private function generateVariabePaymentForm()
    {
        return $this->createFormBuilder()
            ->add('amount', 'money', array(
                'scale' => 4,
                'label' => false
            ))
            ->add('save', 'submit')
            ->getForm();
    }
}