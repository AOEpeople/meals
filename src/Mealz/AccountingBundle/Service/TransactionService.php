<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Service;

use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\AccountingBundle\Service\PayPal\PayPalService;
use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\UserBundle\Entity\ProfileRepository;
use Doctrine\ORM\EntityManagerInterface;
use PayPalHttp\HttpException;
use PayPalHttp\IOException;
use RuntimeException;

class TransactionService
{
    private const PAYMENT_METHOD_PAYPAL = 0;

    private PayPalService $paypalService;
    private ProfileRepository $profileRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(
        PayPalService $paypalService,
        ProfileRepository $profileRepository,
        EntityManagerInterface $entityManager
    ) {
        $this->paypalService = $paypalService;
        $this->profileRepository = $profileRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @throws HttpException
     * @throws IOException
     */
    public function process(string $orderID, string $profileID, float $amount): void
    {
        $order = $this->paypalService->getOrder($orderID);
        $profile = $this->profileRepository->find($profileID);

        if (!($profile instanceof Profile)) {
            throw new RuntimeException('profile not found: '.$profileID, 1633093870);
        }

        if ($amount !== $order->getAmount()) {
            throw new RuntimeException(
                sprintf('payment amount mismatch; %f <> %f', $amount, $order->getAmount()),
                1633094204
            );
        }

        $transaction = new Transaction();
        $transaction->setProfile($profile);
        $transaction->setOrderId($order->getId());
        $transaction->setAmount($order->getAmount());
        $transaction->setDate($order->getDateTime());
        $transaction->setPaymethod(self::PAYMENT_METHOD_PAYPAL);

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();
    }
}
