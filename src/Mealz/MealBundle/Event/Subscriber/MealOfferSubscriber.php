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
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class MealOfferSubscriber implements EventSubscriberInterface
{
    private const PUBLISH_TOPIC = 'meal-offer-updates';
    private const PUBLISH_MSG_TYPE = 'mealOfferUpdate';

    private const OFFER_NEW = 0;
    private const OFFER_ACCEPTED = 1;
    private const OFFER_GONE = 2;

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

    #[Override]
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

        $meal = $event->getMeal();
        $offerCount = $this->offerService->getOfferCountByMeal($meal);

        // we won't send an update for each new offer, rather only when new offers are available
        if (0 > $offerCount) {
            return;
        }

        $parentId = null;
        if (null !== $meal->getDish()->getParent()) {
            $parentId = $meal->getDish()->getParent()->getId();
        }
        $day = $meal->getDay();
        $week = $day->getWeek();

        $this->publisher->publish(
            self::PUBLISH_TOPIC,
            [
                'type' => self::OFFER_NEW,
                'weekId' => $week->getId(),
                'dayId' => $day->getId(),
                'mealId' => $meal->getId(),
                'parentId' => $parentId,
            ],
            self::PUBLISH_MSG_TYPE
        );
    }

    public function onOfferAccepted(MealOfferAcceptedEvent $event): void
    {
        $offerer = $event->getOfferer();
        $participant = $event->getParticipant();

        $this->sendOfferAcceptedNotifications($offerer, $participant);

        $meal = $event->getMeal();
        $day = $meal->getDay();
        $week = $day->getWeek();

        $offerCount = $this->offerService->getOfferCountByMeal($meal);

        $parentId = null;
        if (null !== $meal->getDish()->getParent()) {
            $parentId = $meal->getDish()->getParent()->getId();
        }

        $this->publisher->publish(
            self::PUBLISH_TOPIC,
            [
                'type' => self::OFFER_ACCEPTED,
                'weekId' => $week->getId(),
                'dayId' => $day->getId(),
                'mealId' => $meal->getId(),
                'parentId' => $parentId,
                'participantId' => $participant->getId(),
                'lastOffer' => 0 === $offerCount,
            ],
            self::PUBLISH_MSG_TYPE
        );
    }

    public function onOfferCancelled(MealOfferCancelledEvent $event): void
    {
        $meal = $event->getMeal();
        $offerCount = $this->offerService->getOfferCountByMeal($meal);

        $day = $meal->getDay();
        $week = $day->getWeek();

        $parentId = null;
        if (null !== $meal->getDish()->getParent()) {
            $parentId = $meal->getDish()->getParent()->getId();
        }

        // we won't send an update for each cancellation, rather only when no further meal offers are available
        if (0 < $offerCount) {
            return;
        }

        // send message to the configured messaging service
        $msg = new OfferAcceptedMessage($event->getParticipant(), $this->offerService, $this->translator);
        $this->notifier->send($msg);

        $this->publisher->publish(
            self::PUBLISH_TOPIC,
            [
                'type' => self::OFFER_GONE,
                'weekId' => $week->getId(),
                'dayId' => $day->getId(),
                'mealId' => $meal->getId(),
                'parentId' => $parentId,
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
