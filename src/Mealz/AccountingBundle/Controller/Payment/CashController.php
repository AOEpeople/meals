<?php

namespace App\Mealz\AccountingBundle\Controller\Payment;

use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\MealBundle\Controller\BaseController;
use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_KITCHEN_STAFF')]
final class CashController extends BaseController
{
    public function __construct(private readonly LoggerInterface $logger)
    {
    }

    public function postPaymentCash(
        Profile $profile,
        Request $request,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        try {
            $transaction = new Transaction();
            $transaction->setProfile($profile);
            $amount = (float) $request->query->get('amount');

            if ($amount > 0) {
                $transaction->setAmount($amount);

                $entityManager->persist($transaction);
                $entityManager->flush();

                $this->logger->info('admin added {amount}€ into wallet of {profile} (Transaction: {transactionId})', [
                    'profile' => $transaction->getProfile(),
                    'amount' => $transaction->getAmount(),
                    'transactionId' => $transaction->getId(),
                ]);

                return new JsonResponse($transaction->getAmount(), Response::HTTP_OK);
            } else {
                throw new Exception('601: Amount less than 0');
            }
        } catch (Exception $e) {
            $this->logger->error('transaction create error', $this->getTrace($e));

            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
