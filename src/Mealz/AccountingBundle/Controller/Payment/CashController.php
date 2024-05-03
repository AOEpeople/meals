<?php

namespace App\Mealz\AccountingBundle\Controller\Payment;

use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\MealBundle\Controller\BaseController;
use App\Mealz\UserBundle\Entity\Profile;
use Exception;
use Psr\Log\LoggerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("is_granted('ROLE_KITCHEN_STAFF')")
 */
class CashController extends BaseController
{
    private LoggerInterface $logger;
    public function __construct(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }
    /**
     * @Security("is_granted('ROLE_KITCHEN_STAFF')")
     */
    public function postPaymentCash(Profile $profile, Request $request): JsonResponse
    {
        try {
            $transaction = new Transaction();
            $transaction->setProfile($profile);
            $amount = (float) $request->query->get('amount');

            if ($amount > 0) {
                $transaction->setAmount($amount);

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($transaction);
                $entityManager->flush();

                $logger = $this->logger;
                $logger->info('admin added {amount}€ into wallet of {profile} (Transaction: {transactionId})', [
                    'profile' => $transaction->getProfile(),
                    'amount' => $transaction->getAmount(),
                    'transactionId' => $transaction->getId(),
                ]);

                return new JsonResponse($transaction->getAmount(), \Symfony\Component\HttpFoundation\Response::HTTP_OK);
            } else {
                throw new Exception('601: Amount less than 0');
            }
        } catch (Exception $e) {
            $logger = $this->logger;
            $logger->info($e->getMessage());

            return new JsonResponse(['message' => $e->getMessage()], \Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
