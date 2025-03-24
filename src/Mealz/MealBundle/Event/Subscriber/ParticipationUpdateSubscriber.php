<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Event\Subscriber;

use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Event\ParticipationUpdateEvent;
use App\Mealz\MealBundle\Service\ParticipationCountService;
use App\Mealz\MealBundle\Service\Publisher\PublisherInterface;
use Override;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

final class ParticipationUpdateSubscriber implements EventSubscriberInterface
{
    private const string PUBLISH_TOPIC = 'participation-updates';
    private const string PUBLISH_MSG_TYPE = 'participationUpdate';

    public function __construct(
        private readonly PublisherInterface $publisher,
        private readonly ParticipationCountService $partCountSrv
    ) {
    }

    /**
     * @return string[]
     *
     * @psalm-return array{ParticipationUpdateEvent::class: 'onUpdate'}
     */
    #[Override]
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
        $eventMeal = $event->getParticipant()->getMeal();
        $meals = $eventMeal->getDay()->getMeals();
        if (!$eventMeal->isOpen()) { // do not send updates for past meals
            return;
        }

        $participationsPerDay = $this->partCountSrv->getParticipationByDay($eventMeal->getDay());

        $data = [
            'weekId' => $eventMeal->getDay()->getWeek()->getId(),
            'dayId' => $eventMeal->getDay()->getId(),
            'meals' => [],
            'participant' => $event->getParticipant()->getProfile()->getFullName(),
        ];

        foreach ($meals as $meal) {
            $data['meals'][] = $this->getMealInfo($meal, $participationsPerDay);
        }

        $this->publisher->publish(self::PUBLISH_TOPIC, $data, self::PUBLISH_MSG_TYPE);
    }

    /**
     * @return (bool|int|mixed|null)[]
     *
     * @psalm-return array{mealId: int|null, parentId: int|null, limit: int, reachedLimit: bool, isOpen: bool, isLocked: bool, participations: int<0, max>|mixed}
     */
    private function getMealInfo(Meal $meal, array $participationsPerDay): array
    {
        $participationCount = null;
        if (array_key_exists($meal->getDish()->getSlug(), $participationsPerDay['totalCountByDishSlugs'])) {
            $participationCount = $participationsPerDay['totalCountByDishSlugs'][$meal->getDish()->getSlug()]['count'];
        } else {
            $participationCount = $meal->getParticipants()->count();
        }

        $meal = [
            'mealId' => $meal->getId(),
            'parentId' => $meal->getDish()->getParent() ? $meal->getDish()->getParent()->getId() : null,
            'limit' => $meal->getParticipationLimit(),
            'reachedLimit' => $meal->getParticipationLimit() > 0.0 ? $participationCount >= $meal->getParticipationLimit() : false,
            'isOpen' => $meal->isOpen(),
            'isLocked' => $meal->isLocked(),
            'participations' => $participationCount,
        ];

        return $meal;
    }
}
