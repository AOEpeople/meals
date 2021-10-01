<?php

namespace App\Mealz\AccountingBundle\Controller\Payment;

use App\Mealz\AccountingBundle\Service\TransactionService;
use App\Mealz\AccountingBundle\Service\Wallet;
use App\Mealz\MealBundle\Controller\BaseController;
use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\UserBundle\Entity\Profile;
use Exception;
use JsonException;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;
use App\Mealz\AccountingBundle\Form\EcashPaymentAdminForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

class EcashController extends BaseController
{
    public function getPaymentFormForProfile(Profile $profile, Wallet $wallet): JsonResponse
    {
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
     */
    public function paymentFormHandling(
        Request $request,
        TransactionService $transactionService,
        TranslatorInterface $translator
    ): Response {
        if (false === $request->isMethod('POST')) {
            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        try {
            $transaction = $this->getTransactionData($request);
        } catch (Exception $e) {
            $this->logException($e, 'bad request', ['request_data' => $request->getContent()]);

            return new Response('', Response::HTTP_BAD_REQUEST);
        }

        try {
            $transactionService->process($transaction['orderID'], $transaction['profileID'], $transaction['amount']);
        } catch(Exception $e) {
            $this->logException($e, 'transaction processing error', [
                'orderID' => $transaction['orderID'],
                'profile' => $transaction['profileID'],
                'amount'  => $transaction['amount']
            ]);

            return new Response('', Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        $message = $translator->trans("payment.transaction_history.successful_payment", [], 'messages');
        $this->addFlashMessage($message, 'success');

        return new Response(
            $this->generateUrl('mealz_accounting_payment_transaction_history'),
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );
    }

    public function transactionFailure(TranslatorInterface $translator): Response
    {
        $message = $translator->trans("payment.transaction_history.payment_failed", [], 'messages');
        $severity = 'danger';

        $this->addFlashMessage($message, $severity);

        return $this->redirect($this->generateUrl('mealz_accounting_payment_transaction_history'));
    }

    /**
     * @return array<string, mixed>
     *
     * @throws JsonException
     */
    private function getTransactionData(Request $request): array
    {
        $data = [];
        $payload = $this->decodeJSONPayLoad($request);
        foreach ($payload as $item) {
            $data[$item['name']] = $item['value'];
        }

        if (!isset($data['ecash[orderid]']) || ('' === $data['ecash[orderid]'])) {
            throw new RuntimeException('missing or invalid order-id', 1633095392);
        }
        if (!isset($data['ecash[profile]']) || ('' === $data['ecash[profile]'])) {
            throw new RuntimeException('missing or invalid profile-id', 1633095400);
        }
        if (!isset($data['ecash[amount]']) || (0.0 >= (float) $data['ecash[amount]'])) {
            throw new RuntimeException('invalid amount', 1633095450);
        }

        return [
            'orderID'   => $data['ecash[orderid]'],
            'profileID' => $data['ecash[profile]'],
            'amount'    => (float) $data['ecash[amount]'],
        ];
    }

    /**
     * @return array<string, mixed>
     *
     * @throws JsonException
     */
    private function decodeJSONPayLoad(Request $request): array
    {
        $content = $request->getContent();
        if (!is_string($content) || '' === $content) {
            return [];
        }

        return json_decode($content, true, 512, JSON_THROW_ON_ERROR);
    }
}
