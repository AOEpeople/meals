<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Controller\Payment;

use App\Mealz\AccountingBundle\Service\TransactionService;
use App\Mealz\MealBundle\Controller\BaseController;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Throwable;

#[IsGranted('ROLE_USER')]
class EcashController extends BaseController
{
    private LoggerInterface $logger;

    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    /* public function getPaymentFormForProfile(Profile $profile, Wallet $wallet): Response
    {
        // Default value for E-Cash payment overlay
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

        $template = 'MealzAccountingBundle:Accounting/Payment/Ecash:form_ecash_amount.html.twig';
        $renderedForm = $this->render($template, ['form' => $form->createView()]);

        return new Response($renderedForm->getContent());
    } */

    /**
     * Triggers actions after a PayPal transaction (payment) is successfully completed.
     */
    public function postPayment(
        Request $request,
        TransactionService $transactionService
    ): Response {
        try {
            $transactionService->createFromRequest($request);
        } catch (AccessDeniedHttpException $ade) {
            return new Response('', Response::HTTP_FORBIDDEN);
        } catch (BadRequestHttpException $bre) {
            $this->logPostPaymentException($bre, 'bad request', $request);

            return new Response('', Response::HTTP_BAD_REQUEST);
        } catch (UnprocessableEntityHttpException $uehe) {
            $this->logPostPaymentException($uehe, 'unprocessable entity', $request);

            return new Response('', Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (Exception $e) {
            $this->logPostPaymentException($e, 'transaction create error', $request);

            return new Response($e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new Response(
            '',
            Response::HTTP_OK,
            ['content-type' => 'text/html']
        );
    }

    private function logPostPaymentException(Throwable $exc, string $message, Request $request): void
    {
        $this->logger->error(
            $message,
            [
                'request_method' => $request->getMethod(),
                'request_content' => $request->getContent(),
                'trace' => $this->getTrace($exc),
            ]
        );
    }
}
