<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\MealCollection;
use App\Mealz\MealBundle\Event\ParticipationUpdateEvent;
use App\Mealz\MealBundle\Service\MealAvailabilityService;
use App\Mealz\MealBundle\Service\ParticipationService;
use App\Mealz\MealBundle\Service\Publisher\PublisherInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ParticipationUpdateSubscriber implements EventSubscriberInterface
{
    private const PUBLISH_TOPIC = 'participation-updates';
    private const PUBLISH_MSG_TYPE = 'participationUpdate';

    private MealAvailabilityService $availabilityService;
    private ParticipationService $participationSrv;
    private PublisherInterface $publisher;

    public function __construct(
        MealAvailabilityService $availabilityService,
        ParticipationService $participationSrv,
        PublisherInterface $publisher
    ) {
        $this->availabilityService = $availabilityService;
        $this->participationSrv = $participationSrv;
        $this->publisher = $publisher;
    }

    /**
     * {@inheritDoc}
     */
    public static function getSubscribedEvents(): array
    {
        return [
            ParticipationUpdateEvent::class => 'onUpdate',
        ];
    }

    /**
     * Triggers action when a participation is updated, e.g. join, delete.
     */
    public function onUpdate(ParticipationUpdateEvent $event): void
    {
        $eventParticipation = $event->getParticipant()->getEvent();
        $meal = $event->getParticipant()->getMeal();

        if (null !== $meal) {
            if (!$meal->isOpen()) { // do not send updates for past meals
                return;
            }

            $mealDay = $meal->getDay();
            $mealsAvailability = $this->availabilityService->getByDay($mealDay);
            $dayMeals = $mealDay->getMeals();
            $participationCount = $this->getParticipationCount($dayMeals);
            $data = $this->getParticipationStatus($dayMeals, $mealsAvailability, $participationCount);

            $this->publisher->publish(self::PUBLISH_TOPIC, $data, self::PUBLISH_MSG_TYPE);
        }

        if (null !== $eventParticipation) {

            $participationCount = $this->participationSrv->getCountByEvent($eventParticipation);

            $data[$eventParticipation->getId()] = [
                'count' => $participationCount ?? 0,
                'locked' => false,
                'available' => true,
            ];

            $this->publisher->publish(self::PUBLISH_TOPIC, $data, self::PUBLISH_MSG_TYPE);
        }
    }

    private function getParticipationCount(MealCollection $meals): array
    {
        $count = [];

        /** @var Meal $meal */
        foreach ($meals as $meal) {
            $count[$meal->getId()] = $this->participationSrv->getCountByMeal($meal);
        }

        return $count;
    }

    private function getParticipationStatus(MealCollection $meals, array $availability, array $participationCount): array
    {
        $status = [];

        /** @var Meal $meal */
        foreach ($meals as $meal) {
            $mealId = $meal->getId();
            $mealAvailability = $availability[$mealId] ?? false;

            $mealStatus = [
                'count' => $participationCount[$mealId] ?? 0,
                'locked' => $meal->isLocked(),
                'available' => false,
            ];

            if (is_array($mealAvailability) && $mealAvailability['available']) {
                $mealStatus['available'] = true;
                $mealStatus['availableWith'] = $mealAvailability['availableWith'];
            } else {
                $mealStatus['available'] = $mealAvailability;
            }

            $status[$mealId] = $mealStatus;
        }

        return $status;
    }
}
