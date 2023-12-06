<?php

namespace App\Mealz\AccountingBundle\Controller;

use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\AccountingBundle\Event\ProfileSettlementEvent;
use App\Mealz\AccountingBundle\Repository\TransactionRepositoryInterface;
use App\Mealz\AccountingBundle\Service\Wallet;
use App\Mealz\MealBundle\Controller\BaseController;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\MealBundle\Service\Mailer\MailerInterface;
use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\UserBundle\Repository\ProfileRepositoryInterface;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("is_granted('ROLE_KITCHEN_STAFF')")
 */
class CostSheetController extends BaseController
{
    private MailerInterface $mailer;
    private EventDispatcherInterface $eventDispatcher;

    public function __construct(MailerInterface $mailer, EventDispatcherInterface $eventDispatcher)
    {
        $this->mailer = $mailer;
        $this->eventDispatcher = $eventDispatcher;
    }

    public function list(
        ParticipantRepositoryInterface $participantRepo,
        TransactionRepositoryInterface $transactionRepo
    ): JsonResponse {
        $transactionsPerUser = $transactionRepo->findUserDataAndTransactionAmountForGivenPeriod();
        $users = $participantRepo->findCostsGroupedByUserGroupedByMonth();

        // create column names
        $numberOfMonths = 3;
        $columnNames = ['earlier' => 'earlier'];
        $dateTime = new DateTime("first day of -$numberOfMonths month 00:00");
        $earlierTimestamp = $dateTime->getTimestamp();
        for ($i = 0; $i < $numberOfMonths + 1; ++$i) {
            $columnNames[$dateTime->getTimestamp()] = clone $dateTime;
            $dateTime->modify('+1 month');
        }
        $columnNames['total'] = 'total';

        // create table rows
        foreach ($users as $username => &$user) {
            $userCosts = array_fill_keys(array_keys($columnNames), '0');
            foreach ($user['costs'] as $cost) {
                $monthCosts = $this->getRemainingCosts($cost['costs'], $transactionsPerUser[$username]['amount']);
                if ($cost['timestamp'] < $earlierTimestamp) {
                    $userCosts['earlier'] = (float) bcadd($userCosts['earlier'], $monthCosts, 4);
                } else {
                    $userCosts[$cost['timestamp']] = $monthCosts;
                }
                $userCosts['total'] = (float) bcadd($userCosts['total'], $monthCosts, 4);
            }
            if ($transactionsPerUser[$username]['amount'] > 0) {
                $userCosts['total'] = $transactionsPerUser[$username]['amount'];
            }
            $user['costs'] = $userCosts;

            // if total amount is zero, remove user from rendered items
            if ('0.0000' === $userCosts['total']) {
                unset($users[$username]);
            }
        }

        ksort($users, SORT_STRING);
        unset($columnNames['total']);
        unset($columnNames['earlier']);

        return new JsonResponse([
            'columnNames' => $columnNames,
            'users' => $users,
        ]);
    }

    public function hideUser(Profile $profile): JsonResponse
    {
        if (!$profile->isHidden()) {
            $entityManager = $this->getDoctrine()->getManager();
            $profile->setHidden(true);
            $entityManager->persist($profile);
            $entityManager->flush();

            return new JsonResponse(null, 200);
        } else {
            return new JsonResponse(['message' => 'Profile is already hidden'], 500);
        }
    }

    private function getRemainingCosts($costs, &$transactions)
    {
        $result = bcsub($costs, $transactions, 4);
        $transactions = abs($result);
        if ($result < 0) {
            $transactions = abs($result);
        } else {
            $transactions = 0;
        }

        return ($result < 0) ? 0 : $result * -1;
    }

    public function postSettlement(Profile $profile, Wallet $wallet): JsonResponse
    {
        if (null === $profile->getSettlementHash() && $wallet->getBalance($profile) > 0.00) {
            $username = $profile->getUsername();
            $secret = $this->getParameter('app.secret');
            $hashCode = str_replace('/', '', crypt($username, $secret));
            $urlEncodedHash = urlencode($hashCode);

            $entityManager = $this->getDoctrine()->getManager();
            $profile->setSettlementHash($hashCode);
            $entityManager->persist($profile);
            $entityManager->flush();

            $this->sendSettlementRequestMail($profile, $urlEncodedHash);

            return new JsonResponse(null, 200);
        } elseif (null !== $profile->getSettlementHash() && $wallet->getBalance($profile) > 0.00) {
            return new JsonResponse(['message' => 'Settlement request already send'], 500);
        } else {
            return new JsonResponse(['message' => 'Settlement request failed'], 500);
        }
    }

    public function getProfileFromHash(string $hash, ProfileRepositoryInterface $profileRepository): JsonResponse
    {
        $queryResult = $profileRepository->findBy(['settlementHash' => urldecode($hash)]);
        $profile = $queryResult[0];

        if (null === $profile) {
            return new JsonResponse(['message' => 'Not found'], 404);
        }
        return new JsonResponse([
            'user' => $profile->getUsername(),
            'fullName' => $profile->getFullName(),
            'roles' => $profile->getRoles(),
        ], 200);
    }

    public function renderConfirmButton(string $hash, ProfileRepositoryInterface $profileRepo): Response
    {
        $profile = null;
        $queryResult = $profileRepo->findBy(['settlementHash' => urldecode($hash)]);

        if (true === is_array($queryResult) && false === empty($queryResult)) {
            $profile = $queryResult[0];
        } else {
            $this->addFlashMessage($this->get('translator')->trans('payment.costsheet.account_settlement.confirmation.failure'), 'danger');
        }

        return $this->render('MealzAccountingBundle::confirmationPage.html.twig', [
            'hash' => $hash,
            'profile' => $profile, ]);
    }

    /**
     * @throws Exception
     */
    public function confirmSettlement(
        string $hash,
        ProfileRepositoryInterface $profileRepository,
        Wallet $wallet
    ): Response {
        $queryResult = $profileRepository->findBy(['settlementHash' => urldecode($hash)]);

        if (true === is_array($queryResult) && false === empty($queryResult)) {
            $profile = $queryResult[0];
            $profile->setSettlementHash(null);

            // Dispatch the event
            $this->eventDispatcher->dispatch(new ProfileSettlementEvent($profile));

            $transaction = new Transaction();
            $transaction->setProfile($profile);
            $transaction->setDate(new DateTime());
            $transaction->setAmount(-1 * abs($wallet->getBalance($profile)));

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($profile);
            $entityManager->persist($transaction);
            $entityManager->flush();

            /*
             * for devbox situation, if you are not logged in with fake-login
             * With Keycloak this if condition is not needed anymore
             */
            if (null !== $this->getProfile()) {
                $logger = $this->get('monolog.logger.balance');
                $logger->info(
                    '{hr_member} settled {users} Balance.',
                    [
                        'hr_member' => $this->getProfile()->getFullName(),
                        'users' => $profile->getFullName(),
                    ]
                );
            }

            $message = $this->get('translator')->trans(
                'payment.costsheet.account_settlement.confirmation.success',
                ['%fullname%' => $profile->getFullName()]
            );
            $severity = 'success';
        } else {
            $message = $this->get('translator')->trans('payment.costsheet.account_settlement.confirmation.failure');
            $severity = 'danger';
        }

        $this->addFlashMessage($message, $severity);

        return $this->render('@MealzAccounting/confirmationPage.html.twig', ['profile' => null]);
    }

    private function sendSettlementRequestMail(Profile $profile, string $urlEncodedHash): void
    {
        $translator = $this->get('translator');

        $receiver = (string) $this->getParameter('app.email.settlement_request.receiver');
        $subject = $translator->trans('payment.costsheet.mail.subject', [], 'messages');
        $body = $translator->trans(
            'payment.costsheet.mail.body',
            [
                '%admin%' => $this->getProfile()->getFullName(),
                '%fullname%' => $profile->getFullName(),
                '%link%' => rtrim($this->getParameter('app.base_url'), '/') . $this->generateUrl(
                    'MealzMealBundle_costs_settlement_confirm',
                    ['hash' => $urlEncodedHash]
                ),
            ],
            'messages'
        );

        $this->mailer->send($receiver, $subject, $body, true);
    }
}
