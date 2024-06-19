<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Message;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Message\WeeklyMenuMessage;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class WeeklyMenuMessageTest extends WebTestCase
{
    private TranslatorInterface $translator;

    protected function setUp(): void
    {
        self::bootKernel();

        $this->translator = self::getContainer()->get('translator');
    }

    /**
     * @testdox Mattermost Notification message shows inserted dish of the week
     */
    public function testContainsDish(): void
    {
        $days = [$this->generateDay(['dish'], new DateTime('Monday'))];
        $week = $this->generateWeek($days);

        $message = new WeeklyMenuMessage($week, $this->translator);

        self::assertStringContainsString('dish', $message->getContent());
    }

    /**
     * @testdox If the week is disabled no day should be shown
     */
    public function testWeekDisabled(): void
    {
        $days = [
            $this->generateDay(['dish'], new DateTime('Monday')),
            $this->generateDay(['fish'], new DateTime('Tuesday')),
        ];
        $week = $this->generateWeek($days);
        $week->setEnabled(false);

        $message = new WeeklyMenuMessage($week, $this->translator);
        $content = $message->getContent();

        self::assertStringNotContainsString('Monday', $content);
        self::assertStringNotContainsString('Tuesday', $content);
    }

    /**
     * @testdox If a certain day is disabled, this day should not be shown
     */
    public function testDayDisabled(): void
    {
        $days = [
            $this->generateDay(['Fish'], new DateTime('Monday')),
            $this->generateDay(['Chips'], new DateTime('Tuesday')),
        ];
        $days[0]->setEnabled(false);
        $week = $this->generateWeek($days);

        $message = new WeeklyMenuMessage($week, $this->translator);
        $content = $message->getContent();

        self::assertStringNotContainsString('Fish', $content);
        self::assertStringContainsString('Chips', $content);
    }

    /**
     * @testdox If a Variation is added, the parent should also be added accordingly
     */
    public function testVariationsAddParent(): void
    {
        $day = $this->generateDay(['Fish'], new DateTime('Monday'));

        $dishVariationA = new DishVariation();
        $dishVariationB = new DishVariation();
        $dishParent = new Dish();

        $dishVariationA->setTitleEn('VariationA');
        $dishVariationB->setTitleEn('VariationB');

        $dishParent->setTitleEn('Parent');

        $dishVariationA->setParent($dishParent);
        $dishVariationB->setParent($dishParent);

        $mealA = new Meal($dishVariationA, $day);
        $mealB = new Meal($dishVariationB, $day);

        $day->addMeal($mealA);
        $day->addMeal($mealB);

        $week = $this->generateWeek([$day]);

        $message = new WeeklyMenuMessage($week, $this->translator);
        $content = $message->getContent();

        self::assertStringContainsString('Parent', $content);
        self::assertStringContainsString('(VariationA, VariationB)', $content);
    }

    /**
     * @param Day[] $days
     */
    private function generateWeek(array $days): Week
    {
        $week = new Week();
        $week->setDays(new ArrayCollection($days));

        return $week;
    }

    private function generateDay(array $dishTitles, DateTime $date): Day
    {
        $day = new Day();
        $day->setDateTime($date);

        foreach ($dishTitles as $dishTitle) {
            $dish = new Dish();
            $dish->setTitleEn($dishTitle);

            $meal = new Meal($dish, $day);
            $day->addMeal($meal);
        }

        return $day;
    }
}
