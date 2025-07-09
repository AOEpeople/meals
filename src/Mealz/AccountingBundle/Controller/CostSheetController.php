<?php

namespace App\Mealz\AccountingBundle\Controller;

use App\Mealz\AccountingBundle\Entity\Transaction;
use App\Mealz\AccountingBundle\Event\ProfileSettlementEvent;
use App\Mealz\AccountingBundle\Repository\TransactionRepositoryInterface;
use App\Mealz\AccountingBundle\Service\CostSheetService;
use App\Mealz\AccountingBundle\Service\Wallet;
use App\Mealz\MealBundle\Controller\BaseController;
use App\Mealz\MealBundle\Repository\ParticipantRepositoryInterface;
use App\Mealz\MealBundle\Service\Mailer\MailerInterface;
use App\Mealz\UserBundle\Entity\Profile;
use App\Mealz\UserBundle\Repository\ProfileRepositoryInterface;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class CostSheetController extends BaseController
{
    public function __construct(
        private readonly CostSheetService $costSheetService,
        private readonly MailerInterface $mailer,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly LoggerInterface $logger,
        private readonly TranslatorInterface $translator
    ) {
    }

    #[IsGranted('ROLE_KITCHEN_STAFF')]
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
                    $userCosts['earlier'] = bcadd($userCosts['earlier'], $monthCosts, 4);
                } else {
                    $userCosts[$cost['timestamp']] = $monthCosts;
                }
                $userCosts['total'] = bcadd($userCosts['total'], $monthCosts, 4);
            }
            if ($transactionsPerUser[$username]['amount'] > 0) {
                $userCosts['total'] = $transactionsPerUser[$username]['amount'];
            }
            $user['costs'] = array_map(
                fn ($cost) => (float) $cost,
                $userCosts
            );
        }

        $users = $this->costSheetService->mergeDoubleUserTransactions($users);

        ksort($users, SORT_STRING);
        unset($columnNames['total']);
        unset($columnNames['earlier']);

        return new JsonResponse([
            'columnNames' => $columnNames,
            'users' => $users,
        ]);
    }

    private function getRemainingCosts(string $costs, ?string &$transactions): string
    {
        if (null !== $transactions) {
            $result = (float) bcsub($costs, $transactions, 4);
        } else {
            $result = (float) $costs;
        }

        if ($result < 0) {
            $transactions = strval(abs($result));
        } else {
            $transactions = '0';
        }

        return ($result < 0) ? '0' : strval($result * -1);
    }

    #[IsGranted('ROLE_KITCHEN_STAFF')]
    public function hideUser(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $parameters = json_decode($request->getContent(), true);
        try {
            $profile = $this->getProfileFromUsername($parameters, $entityManager);
            if (!$profile->isHidden()) {
                $profile->setHidden(true);
                $entityManager->persist($profile);
                $entityManager->flush();

                return new JsonResponse(null, Response::HTTP_OK);
            }

            return new JsonResponse(['message' => '501: Profile is already hidden'], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (EntityNotFoundException $exeption) {
            return new JsonResponse(['message' => '506: ' . $exeption->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    #[IsGranted('ROLE_KITCHEN_STAFF')]
    public function postSettlement(
        Request $request,
        Wallet $wallet,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $parameters = json_decode($request->getContent(), true);
        try {
            $profile = $this->getProfileFromUsername($parameters, $entityManager);
            if (null === $profile->getSettlementHash() && $wallet->getBalance($profile) > 0.00) {
                $username = $profile->getUsername();
                $secret = $this->getParameter('app.secret');
                $hashCode = str_replace('/', '', crypt($username, $secret));
                $urlEncodedHash = urlencode($hashCode);

                $profile->setSettlementHash($hashCode);
                $entityManager->persist($profile);
                $entityManager->flush();

                $this->sendSettlementRequestMail($profile, $urlEncodedHash);

                return new JsonResponse(null, Response::HTTP_OK);
            } elseif (null !== $profile->getSettlementHash() && $wallet->getBalance($profile) > 0.00) {
                return new JsonResponse(['message' => '502: Settlement request already send'],
                    Response::HTTP_INTERNAL_SERVER_ERROR);
            } else {
                return new JsonResponse(['message' => '503: Settlement request failed'],
                    Response::HTTP_INTERNAL_SERVER_ERROR);
            }
        } catch (EntityNotFoundException $exeption) {
            return new JsonResponse(['message' => '506: ' . $exeption->getMessage()], Response::HTTP_NOT_FOUND);
        }
    }

    #[IsGranted('ROLE_KITCHEN_STAFF')]
    private function sendSettlementRequestMail(Profile $profile, string $urlEncodedHash): void
    {
        $translator = $this->translator;

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

    #[IsGranted('ROLE_USER')]
    public function getProfileFromHash(string $hash, ProfileRepositoryInterface $profileRepository): JsonResponse
    {
        $profile = $profileRepository->findOneBy(['settlementHash' => urldecode($hash)]);

        if (null === $profile) {
            return new JsonResponse(['message' => '504: Not found'], Response::HTTP_NOT_FOUND);
        }

        return new JsonResponse([
            'user' => $profile->getUsername(),
            'fullName' => $profile->getFullName(),
            'roles' => $profile->getRoles(),
        ], Response::HTTP_OK);
    }

    #[IsGranted('ROLE_USER')]
    public function confirmSettlement(
        string $hash,
        ProfileRepositoryInterface $profileRepository,
        Wallet $wallet,
        EntityManagerInterface $entityManager
    ): JsonResponse {
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

            $entityManager->persist($profile);
            $entityManager->persist($transaction);
            $entityManager->flush();

            $this->logger->info(
                '{hr_member} settled {users} Balance.',
                [
                    'hr_member' => $this->getProfile()->getFullName(),
                    'users' => $profile->getFullName(),
                ]
            );
        } else {
            return new JsonResponse(['message' => '505: Settlement request invalid or already processed'],
                Response::HTTP_INTERNAL_SERVER_ERROR);
        }

        return new JsonResponse(null, Response::HTTP_OK);
    }

    private function getProfileFromUsername(array $parameters, EntityManagerInterface $em): ?Profile
    {
        try {
            $username = $parameters['username'];
            $profileRepo = $em->getRepository(Profile::class);
            $profile = $profileRepo->findOneBy(['username' => $username]);

            return $profile;
        } catch (Exception $exception) {
            throw new EntityNotFoundException('User not found');
        }
    }
}
