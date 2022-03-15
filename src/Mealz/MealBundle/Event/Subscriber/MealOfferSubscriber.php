<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Event\MealOfferAcceptedEvent;
use App\Mealz\MealBundle\Event\MealOfferedEvent;
use App\Mealz\MealBundle\Event\MealOfferCancelledEvent;
use App\Mealz\MealBundle\Service\Mailer\Mailer;
use App\Mealz\MealBundle\Service\Mailer\MailerInterface;
use App\Mealz\MealBundle\Service\Notification\NotifierInterface;
use App\Mealz\MealBundle\Service\OfferService;
use App\Mealz\MealBundle\Service\Publisher\Publisher;
use App\Mealz\MealBundle\Service\Publisher\PublisherInterface;
use App\Mealz\UserBundle\Entity\Profile;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MealOfferSubscriber implements EventSubscriberInterface
{
    private LoggerInterface $logger;
    private Mailer $mailer;
    private NotifierInterface $notifier;
    private OfferService $offerService;
    private PublisherInterface $publisher;
    private TranslatorInterface $translator;

    public function __construct(
        LoggerInterface $logger,
        MailerInterface $mailer,
        NotifierInterface $notifier,
        OfferService $offerService,
        PublisherInterface $publisher,
        TranslatorInterface $translator
    ) {
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->offerService = $offerService;
        $this->notifier = $notifier;
        $this->publisher = $publisher;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            MealOfferedEvent::class => 'onNewOffer',
            MealOfferAcceptedEvent::class => 'onOfferAccepted',
            MealOfferCancelledEvent::class => 'onOfferCancelled',
        ];
    }

    public function onNewOffer(MealOfferedEvent $event): void
    {
        $this->publish([
            'state' => 'new',
            'mealId' => $event->getMeal()->getId(),
        ]);
    }

    public function onOfferAccepted(MealOfferAcceptedEvent $event): void
    {
        $meal = $event->getMeal();
        $offerer = $event->getOfferer();
        $this->sendOfferAcceptedNotifications($offerer, $meal);

        $offerCount = $this->offerService->getOfferCountByMeal($meal);
        $this->publish([
            'state' => 'accepted',
            'mealId' => $meal->getId(),
            'participantId' => $event->getParticipant()->getId(),
            'available' => (0 < $offerCount),
        ]);
    }

    public function onOfferCancelled(MealOfferCancelledEvent $event): void
    {
        $meal = $event->getMeal();
        $offerCount = $this->offerService->getOfferCountByMeal($meal);

        // we won't send an update for each cancellation, rather only when no further meal offers are available
        if (1 < $offerCount) {
            return;
        }

        $this->publish([
            'state' => 'gone',
            'mealId' => $meal->getId(),
        ]);
    }

    private function publish(array $data): void
    {
        $published = $this->publisher->publish(Publisher::TOPIC_MEAL_OFFERS, $data);

        if (!$published) {
            $this->logger->error('publish failure', ['topic' => Publisher::TOPIC_MEAL_OFFERS]);
        }
    }

    /**
     * Sends all the notifications on successful acceptance of an offer.
     *
     * @param Profile $offerer User who offered the meal
     */
    private function sendOfferAcceptedNotifications(Profile $offerer, Meal $meal): void
    {
        $dish = $meal->getDish();
        $dishTitle = $dish->getTitleEn();

        $parentDish = $dish->getParent();
        if (null !== $parentDish) {
            $dishTitle = $parentDish->getTitleEn() . ' ' . $dishTitle;
        }

        $this->sendOfferAcceptedEmail($offerer->getProfile(), $dishTitle);

        $remainingOfferCount = $this->offerService->getOfferCount($meal->getDateTime());
        $this->sendOfferAcceptedMessage($dishTitle, $remainingOfferCount);
    }

    /**
     * Sends an email to the offerer that some has taken over his offered meal.
     *
     * @param Profile $profile   Offerer profile
     * @param string  $dishTitle Offered dish title
     */
    private function sendOfferAcceptedEmail(Profile $profile, string $dishTitle): void
    {
        $recipient = $profile->getUsername() . $this->translator->trans('mail.domain', [], 'messages');
        $subject = $this->translator->trans('mail.subject', [], 'messages');

        $message = $this->translator->trans(
            'mail.message',
            [
                '%firstname%' => $profile->getFirstname(),
                '%takenOffer%' => $dishTitle,
            ],
            'messages'
        );

        $this->mailer->send($recipient, $subject, $message);
    }

    /**
     * Sends a message to the configured messaging service that an offer has been accepted, i.e. gone.
     */
    private function sendOfferAcceptedMessage(string $dishTitle, int $remainingOfferCount): void
    {
        $this->notifier->sendAlert(
            $this->translator->trans(
                'mattermost.offer_taken',
                [
                    '%count%' => $remainingOfferCount,
                    '%counter%' => $remainingOfferCount,
                    '%takenOffer%' => $dishTitle,
                ],
                'messages'
            )
        );
    }
}
