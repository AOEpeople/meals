<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Service\Notification\MealsNotificationService;
use App\Mealz\MealBundle\Service\Notification\NotifierInterface;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Prophecy\Argument;
use Prophecy\Prophecy\ObjectProphecy;
use Prophecy\Prophet;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Contracts\Translation\TranslatorInterface;

class MealsNotificationServiceTest extends WebTestCase
{
    private ObjectProphecy $mockNotifier;
    private TranslatorInterface $translator;

    protected function setUp(): void
    {
        self::bootKernel();

        $prophet = new Prophet();
        $this->mockNotifier = $prophet->prophesize(NotifierInterface::class);
        $this->translator = self::$container->get('translator');
    }

    /**
     * @testdox Mattermost Notification message shows inserted dish of the week
     */
    public function testContainsDish(): void
    {
        $days = new ArrayCollection([
            $this->generateDay(['dish'], new DateTime('Monday')),
        ]);

        $week = $this->generateWeek($days);

        $this->mockNotifier->sendAlert(Argument::that(static function (string $msg) {
            self::assertStringContainsString('dish', $msg);
        }));
        $mockedService = new MealsNotificationService($this->mockNotifier->reveal(), $this->translator);

        $mockedService->sendWeeklyMenuUpdate($week);
    }

    /**
     * @testdox If the week is disabled no day should be shown
     */
    public function testWeekDisabled(): void
    {
        $days = new ArrayCollection([
            $this->generateDay(['dish'], new DateTime('Monday')),
            $this->generateDay(['fish'], new DateTime('Tuesday')),
        ]);
        $week = $this->generateWeek($days);
        $week->setEnabled(false);

        $this->mockNotifier->sendAlert(Argument::that(static function (string $msg) {
            self::assertStringNotContainsString('Monday', $msg);
            self::assertStringNotContainsString('Tuesday', $msg);
        }));
        $mockedService = new MealsNotificationService($this->mockNotifier->reveal(), $this->translator);

        $mockedService->sendWeeklyMenuUpdate($week);
    }

    /**
     * @testdox If a certain day is disabled, this day should not be shown
     */
    public function testDayDisabled(): void
    {
        $days = new ArrayCollection([
            $this->generateDay(['Fish'], new DateTime('Monday')),
            $this->generateDay(['Chips'], new DateTime('Tuesday')),
        ]);

        $days->first()->setEnabled(false);

        $week = $this->generateWeek($days);

        $this->mockNotifier->sendAlert(Argument::that(static function (string $msg) {
            self::assertStringNotContainsString('Fish', $msg);
            self::assertStringContainsString('Chips', $msg);
        }));
        $mockedService = new MealsNotificationService($this->mockNotifier->reveal(), $this->translator);

        $mockedService->sendWeeklyMenuUpdate($week);
    }

    /**
     * @testdox If a Variation is added, the parent should also be added accordingly
     */
    public function testVariationsAddParent(): void
    {
        $days = new ArrayCollection([
            $this->generateDay(['Fish'], new DateTime('Monday')),
        ]);

        $dishVariationA = new DishVariation();
        $dishVariationB = new DishVariation();
        $dishParent = new Dish();

        $dishVariationA->setTitleEn('VariationA');
        $dishVariationB->setTitleEn('VariationB');

        $dishParent->setTitleEn('Parent');

        $dishVariationA->setParent($dishParent);
        $dishVariationB->setParent($dishParent);

        $day = $days->first();
        $mealA = new Meal($dishVariationA, $day);
        $mealB = new Meal($dishVariationB, $day);

        $day->addMeal($mealA);
        $day->addMeal($mealB);

        $week = $this->generateWeek($days);

        $this->mockNotifier->sendAlert(Argument::that(static function (string $msg) {
            self::assertStringContainsString('Parent', $msg);
            self::assertStringContainsString('(VariationA, VariationB)', $msg);
        }));
        $mockedService = new MealsNotificationService($this->mockNotifier->reveal(), $this->translator);

        $mockedService->sendWeeklyMenuUpdate($week);
    }

    private function generateWeek(ArrayCollection $days): Week
    {
        $week = new Week();

        $week->setDays($days);

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
