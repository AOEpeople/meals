<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Service;

use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\AccountingBundle\Service\Exception\BadDataException;
use App\Mealz\AccountingBundle\Service\Exception\ResourceNotFoundException;
use App\Mealz\AccountingBundle\Service\PayPal\Order;
use App\Mealz\AccountingBundle\Service\PayPal\PayPalService;
use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use JsonException;
use RuntimeException;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

final class TransactionService
{
    private const PAYMENT_METHOD_PAYPAL = '0';

    private const EXCEPTION_ORDER_NOT_COMPLETED = 1633519677;

    private PayPalService $paypalService;
    private EntityManagerInterface $entityManager;
    private Security $security;

    public function __construct(
        PayPalService $paypalService,
        EntityManagerInterface $entityManager,
        Security $security
    ) {
        $this->paypalService = $paypalService;
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function createFromRequest(Request $request): void
    {
        $profile = $this->getProfile();

        try {
            $orderID = $this->getOrderID($request->getContent());
        } catch (BadDataException $e) {
            throw new BadRequestHttpException('', $e);
        }

        try {
            $this->create($orderID, $profile);
        } catch (ResourceNotFoundException $rnf) {
            throw new UnprocessableEntityHttpException('', $rnf, 1633509057);
        } catch (RuntimeException $rte) {
            if (self::EXCEPTION_ORDER_NOT_COMPLETED === $rte->getCode()) {
                throw new UnprocessableEntityHttpException('', $rte, 1633513408);
            }

            throw $rte;
        }
    }

    /**
     * @throws ResourceNotFoundException
     */
    public function create(string $orderID, Profile $profile): void
    {
        try {
            $order = $this->paypalService->getOrder($orderID);
        } catch (Exception $e) {
            throw new RuntimeException('get order error, order-id: ' . $orderID, 1633425633, $e);
        }

        if (null === $order) {
            throw new ResourceNotFoundException('order-id: ' . $orderID);
        }

        if (!$order->isCompleted()) {
            throw new RuntimeException('order not completed, order-id: ' . $order->getId(), self::EXCEPTION_ORDER_NOT_COMPLETED);
        }

        $this->createTransaction($order, $profile);
    }

    private function createTransaction(Order $order, Profile $profile): void
    {
        $transaction = new Transaction();
        $transaction->setProfile($profile);
        $transaction->setOrderId($order->getId());
        $transaction->setAmount($order->getAmount());
        $transaction->setDate($order->getDateTime());
        $transaction->setPaymethod(self::PAYMENT_METHOD_PAYPAL);

        $this->entityManager->persist($transaction);
        $this->entityManager->flush();
    }

    /**
     * @throws BadDataException
     */
    private function getOrderID(string $jsonPayload): string
    {
        $data = [];
        $payload = $this->jsonDecode($jsonPayload);

        foreach ($payload as $item) {
            if (!is_array($item) || !isset($item['name'], $item['value'])) {
                throw new BadDataException('expected array with name, value keys, got ' . print_r($item, true), 1633436079);
            }
            $data[$item['name']] = $item['value'];
        }

        if (!isset($data['ecash[orderid]']) || ('' === $data['ecash[orderid]'])) {
            throw new BadDataException('missing or invalid order-id', 1633095392);
        }

        return $data['ecash[orderid]'];
    }

    private function getProfile(): Profile
    {
        $profile = $this->security->getUser();

        if (null === $profile) {
            throw new AccessDeniedHttpException('login required');
        }

        if (!($profile instanceof Profile)) {
            if (!method_exists($profile, 'getProfile')) {
                throw new AccessDeniedHttpException();
            }

            $profile = $profile->getProfile();
            if (!($profile instanceof Profile)) {
                throw new AccessDeniedHttpException();
            }
        }

        return $profile;
    }

    /**
     * @return array<string, mixed>
     *
     * @throws BadDataException
     */
    private function jsonDecode(string $json): array
    {
        if (!is_string($json) || '' === $json) {
            return [];
        }

        try {
            $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            throw new BadDataException('json decode error', 1633509529, $e);
        }

        return $data;
    }
}
