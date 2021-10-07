<?php

namespace App\Mealz\AccountingBundle\Controller\Payment;

use App\Mealz\AccountingBundle\Service\TransactionService;
use App\Mealz\AccountingBundle\Service\Wallet;
use App\Mealz\MealBundle\Controller\BaseController;
use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\UserBundle\Entity\Profile;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use App\Mealz\AccountingBundle\Form\EcashPaymentAdminForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
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
     * Triggers actions after a PayPal transaction (payment) is successfully completed.
     *
     * @Security("has_role('ROLE_USER')")
     */
    public function postPayment(
        Request $request,
        TransactionService $transactionService,
        TranslatorInterface $translator
    ): Response {
        try {
            $transactionService->createFromRequest($request);
        } catch(AccessDeniedHttpException $ade) {
            return new Response('', Response::HTTP_FORBIDDEN);
        } catch(BadRequestHttpException $bre) {
            $this->logException($bre, 'bad request');

            return new Response('', Response::HTTP_BAD_REQUEST);
        } catch (UnprocessableEntityHttpException $uehe) {
            $this->logException($uehe, 'unprocessable entity');

            return new Response('', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch(Exception $e) {
            $this->logException($e, 'transaction create error');

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
}
