<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Event\MealOfferAcceptedEvent;
use App\Mealz\MealBundle\Event\MealOfferCancelledEvent;
use App\Mealz\MealBundle\Event\MealOfferedEvent;
use App\Mealz\MealBundle\Message\NewOfferMessage;
use App\Mealz\MealBundle\Message\OfferAcceptedMessage;
use App\Mealz\MealBundle\Service\Mailer\MailerInterface;
use App\Mealz\MealBundle\Service\Notification\NotifierInterface;
use App\Mealz\MealBundle\Service\OfferService;
use App\Mealz\MealBundle\Service\Publisher\PublisherInterface;
use App\Mealz\UserBundle\Entity\Profile;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class MealOfferSubscriber implements EventSubscriberInterface
{
    private const PUBLISH_TOPIC = 'meal-offer-updates';
    private const PUBLISH_MSG_TYPE = 'mealOfferUpdate';

    private MailerInterface $mailer;
    private NotifierInterface $notifier;
    private OfferService $offerService;
    private PublisherInterface $publisher;
    private TranslatorInterface $translator;

    public function __construct(
        MailerInterface $mailer,
        NotifierInterface $mealOfferNotifier,
        OfferService $offerService,
        PublisherInterface $publisher,
        TranslatorInterface $translator
    ) {
        $this->mailer = $mailer;
        $this->offerService = $offerService;
        $this->notifier = $mealOfferNotifier;
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
        $msg = new NewOfferMessage($event->getParticipant(), $this->offerService, $this->translator);
        $this->notifier->send($msg);

        $this->publisher->publish(
            self::PUBLISH_TOPIC,
            [
                'state' => 'new',
                'mealId' => $event->getMeal()->getId(),
            ],
            self::PUBLISH_MSG_TYPE
        );
    }

    public function onOfferAccepted(MealOfferAcceptedEvent $event): void
    {
        $meal = $event->getMeal();
        $offerer = $event->getOfferer();
        $participant = $event->getParticipant();
        $this->sendOfferAcceptedNotifications($offerer, $participant);

        $offerCount = $this->offerService->getOfferCountByMeal($meal);
        $this->publisher->publish(
            self::PUBLISH_TOPIC,
            [
                'state' => 'accepted',
                'mealId' => $meal->getId(),
                'participantId' => $event->getParticipant()->getId(),
                'available' => (0 < $offerCount),
            ],
            self::PUBLISH_MSG_TYPE
        );
    }

    public function onOfferCancelled(MealOfferCancelledEvent $event): void
    {
        $meal = $event->getMeal();
        $offerCount = $this->offerService->getOfferCountByMeal($meal);

        // we won't send an update for each cancellation, rather only when no further meal offers are available
        if (1 < $offerCount) {
            return;
        }

        // send message to the configured messaging service
        $msg = new OfferAcceptedMessage($event->getParticipant(), $this->offerService, $this->translator);
        $this->notifier->send($msg);

        $this->publisher->publish(
            self::PUBLISH_TOPIC,
            [
                'state' => 'gone',
                'mealId' => $meal->getId(),
            ],
            self::PUBLISH_MSG_TYPE
        );
    }

    /**
     * Sends all the notifications on successful acceptance of an offer.
     *
     * @param Profile $offerer User who offered the meal
     */
    private function sendOfferAcceptedNotifications(Profile $offerer, Participant $participant): void
    {
        $dish = $participant->getMeal()->getDish();
        $dishTitle = $dish->getTitleEn();

        $parentDish = $dish->getParent();
        if (null !== $parentDish) {
            $dishTitle = $parentDish->getTitleEn() . ' ' . $dishTitle;
        }

        $this->sendOfferAcceptedEmail($offerer->getProfile(), $dishTitle);

        // send message to the configured messaging service
        $msg = new OfferAcceptedMessage($participant, $this->offerService, $this->translator);
        $this->notifier->send($msg);
    }

    /**
     * Sends an email to the offerer that some has taken over his offered meal.
     *
     * @param Profile $profile   Offerer profile
     * @param string  $dishTitle Offered dish title
     */
    private function sendOfferAcceptedEmail(Profile $profile, string $dishTitle): void
    {
        if (null === $profile->getEmail()) {
            return;
        }

        $recipient = $profile->getEmail();
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
}
