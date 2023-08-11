<?php

namespace App\Mealz\AccountingBundle\Controller\Payment;

use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\AccountingBundle\Form\CashPaymentAdminForm;
use App\Mealz\AccountingBundle\Repository\TransactionRepositoryInterface;
use App\Mealz\AccountingBundle\Service\Wallet;
use App\Mealz\MealBundle\Controller\BaseController;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\ORM\EntityManager;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("is_granted('ROLE_KITCHEN_STAFF')")
 */
class CashController extends BaseController
{
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

                $logger = $this->get('monolog.logger.balance');
                $logger->info('admin added {amount}€ into wallet of {profile} (Transaction: {transactionId})', [
                    'profile' => $transaction->getProfile(),
                    'amount' => $transaction->getAmount(),
                    'transactionId' => $transaction->getId(),
                ]);

                return new JsonResponse($transaction->getAmount(), 200);
            } else {
                throw new Exception('Amount less than 0');
            }

        } catch (Exception $e) {
            $logger = $this->get('monolog.logger.balance');
            $logger->info($e->getMessage());

            return new JsonResponse(['message' => $e->getMessage()], 500);
        }
    }
}