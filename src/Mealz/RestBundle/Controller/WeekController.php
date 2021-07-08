<?php

namespace Mealz\RestBundle\Controller;

use Mealz\MealBundle\Entity\Day;
use Mealz\MealBundle\Entity\Meal;
use stdClass;

class WeekController extends BaseController
{
    public function activeAction()
    {
        $this->checkUser();

        $current = $this->getWeekRepository()->getCurrentWeek(array(
            'load_participants' => false
        ));

        $next = $this->getWeekRepository()->getNextWeek(null, array(
            'load_participants' => false
        ));

        return array(
            "currentWeek" => $this->formatWeek($current),
            "nextWeek" => $this->formatWeek($next)
        );
    }

    private function formatWeek($week)
    {
        if (!is_object($week)) {
            return new stdClass();
        }
        $data = array(
            'enabled' => $week->isEnabled(),
            'id' => $week->getId(),
            'year' => $week->getYear(),
            'calendar_week' => $week->getCalendarWeek(),
            'days' => array()
        );

        foreach ($week->getDays() as $day) {
            /** @var Day $day */
            array_push($data['days'], array(
                'id' => $day->getId(),
                'enabled' => $day->isEnabled(),
                'date_time' => $day->getDateTime(),
                'meals' => array()
            ));

            foreach ($day->getMeals() as $meal) {
                /** @var Meal $meal */
                $dish = $meal->getDish();
                $category = $dish->getCategory();
                $participation = $meal->getParticipant($this->getUser()->getProfile());
                array_push($data['days'][count($data['days']) - 1]['meals'], array(
                    'id' => $meal->getId(),
                    'price' => $meal->getPrice(),
                    'date_time' => $meal->getDateTime(),
                    'participantsCount' => $meal->getParticipants()->count(),
                    'participationId' => $participation !== null ? $participation->getId() : null,
                    'isParticipate' => $participation !== null,
                    'dish' => array(
                        'id' => $dish->getId(),
                        'enabled' => $dish->isEnabled(),
                        'slug' => $dish->getSlug(),
                        'title_en' => $dish->getTitleEn(),
                        'title_de' => $dish->getTitleDe(),
                        'price' => $dish->getPrice(),
                        'category' => array(
                            'id' => $category !== null ? $category->getId() : null,
                            'slug' => $category !== null ? $category->getSlug() : null,
                            'title_en' => $category !== null ? $category->getTitleEn() : null,
                            'title_de' => $category !== null ? $category->getTitleDe() : null,
                        )
                    )
                ));
            }
        }
        return $data;
    }
}
