<?php

namespace App\Mealz\AccountingBundle\Controller\Payment;

use Doctrine\ORM\EntityManager;
use App\Mealz\AccountingBundle\Service\Wallet;
use App\Mealz\MealBundle\Controller\BaseController;
use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\UserBundle\Entity\Profile;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use App\Mealz\AccountingBundle\Form\EcashPaymentAdminForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use PayPalCheckoutSdk\Orders\OrdersGetRequest;
use Symfony\Component\HttpFoundation\Response;

class EcashController extends BaseController
{
    public function getPaymentFormForProfile(Profile $profile, Wallet $wallet): JsonResponse {
        $profileRepository = $this->getDoctrine()->getRepository('MealzUserBundle:Profile');
        $profile = $profileRepository->find($profile);

        // Default value for Ecash payment overlay
        $balance = $wallet->getBalance($profile) * (-1);
        if ($balance <= 0) {
            $balance = 0;
        }

        $form = $this->createForm(
            EcashPaymentAdminForm::class,
            new Transaction(),
            [
                'profile' => $profile,
                'balance' => $balance,
            ]
        );

        $template = "MealzAccountingBundle:Accounting/Payment/Ecash:form_ecash_amount.html.twig";
        $renderedForm = $this->render($template, ['form' => $form->createView()]);

        return new JsonResponse($renderedForm->getContent());
    }

    /**
     * Handle the payment form for payments via PayPal
     *
     * @throws Exception
     */
    public function paymentFormHandling(Request $request): Response
    {
        $formArray = [];
        if ($content = $request->getContent()) {
            // Decode the JSON object and insert the content into an array
            foreach (json_decode($content, true) as $formValue) {
                $formArray[$formValue['name']] = $formValue['value'];
            }
        }

        $translator = $this->get('translator');
        $validatedPaypalTrans = null;

        if ($this->isFormValid($formArray) === true) {
            $validatedPaypalTrans = $this->validatePaypalTransaction($formArray);
        }

        // Check if required fields are set and the pay method is set to PayPal ('paymethod' == 0)
        if ($request->isMethod('POST') === true
            && array_key_exists('statuscode', $validatedPaypalTrans) === true
            && array_key_exists('amount', $validatedPaypalTrans) === true
            && $validatedPaypalTrans['statuscode'] === 200) {

            /** @var EntityManager $entityManager */
            $entityManager = $this->getDoctrine()->getManager();
            $profileRepository = $this->getDoctrine()->getRepository('MealzUserBundle:Profile');
            $profile = $profileRepository->find($formArray['ecash[profile]']);

            // Create new transaction with data from given form
            $transaction = new Transaction();
            $transaction->setProfile($profile);
            $transaction->setOrderId($formArray['ecash[orderid]']);
            $transaction->setAmount((float)str_replace(',', '.', $validatedPaypalTrans['amount']));
            $transaction->setDate(new \DateTime());
            $transaction->setPaymethod(0);

            $entityManager->persist($transaction);
            $entityManager->flush();

            $message = $translator->trans("payment.transaction_history.successful_payment", [], 'messages');
            $severity = 'success';
        } else {
            $message = $translator->trans("payment.transaction_history.payment_failed", [], 'messages');
            $severity = 'danger';
        }

        $this->addFlashMessage($message, $severity);

        return new Response(
            $this->generateUrl('mealz_accounting_payment_transaction_history'),
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );
    }

    /**
     * Helper function to validate the PayPal transaction using the PayPal API
     * Note: This function is public so that it can be mocked in the according
     * PHPUnit test class
     *
     * @param Form  $formArray  The form array
     *
     * @return array|null Returns int StatusCode and float Value
     */
    public function validatePaypalTransaction($formArray): ?array
    {
        $paypalId = $this->container->get('twig')->getGlobals()['paypal_id'];
        $secret = $this->container->get('twig')->getGlobals()['paypal_secret'];
        $environment = $this->getParameter('kernel.environment');
        PaypalClient::setCredentialsAndEnvironment($paypalId, $secret, $environment);

        $response = PaypalClient::client()->execute(new OrdersGetRequest($formArray['ecash[orderid]']));

        if (property_exists($response, 'statusCode') === false
            || property_exists($response, 'result') === false
            || property_exists($response->result, 'purchase_units') === false) {
            return null;
        }

        return [
            'statuscode' => $response->statusCode,
            'amount'     => $response->result->purchase_units[0]->amount->value
        ];
    }

    /**
     * Helper function to check if given form is valid
     */
    private function isFormValid(array $formArray): bool
    {
        return (
            !empty($formArray['ecash[orderid]'])
            && !empty($formArray['ecash[profile]'])
            && ((float)(str_replace(',', '.', $formArray['ecash[amount]'])) > 0.00)
        );
    }
}
