<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Tests\Service;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Entity\Meal;
use App\Mealz\MealBundle\Entity\Week;
use App\Mealz\MealBundle\Service\Notification\MealsNotificationService;
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
        parent::setUp();
        self::bootKernel();

        $prophet = new Prophet();
        $this->mockNotifier = $prophet->prophesize('App\Mealz\MealBundle\Service\Notification\NotifierInterface');
        $this->translator = self::$container->get('translator');
    }

    // if a day contains a dish, then it should appear and vice versa
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

    // if the week is disabled, then no days should be shown
    public function testWeekDisabled(): void
    {
        $days = new ArrayCollection([
            $this->generateDay(['dish'], new DateTime('Monday')),
            $this->generateDay(['fish'], new DateTime('Tuesday')),
        ]);
        $week = $this->generateWeek($days);
        $week->setEnabled(false);

        $this->mockNotifier->sendAlert(Argument::that(static function (string $msg) {
            self::assertStringNotContainsString('Tuesday', $msg);
        }));
        $mockedService = new MealsNotificationService($this->mockNotifier->reveal(), $this->translator);

        $mockedService->sendWeeklyMenuUpdate($week);
    }

    // if a day is disabled, then the corresponding dish should not appear
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

    // if a Variation is added, it should also add its parent and categorize as Variation beneath it
    public function testVariationsAddParent(): void
    {
        $days = new ArrayCollection([
            $this->generateDay(['Fish'], new DateTime('Monday')),
        ]);

        $dishVariationA = new DishVariation();
        $dishVariationB = new DishVariation();
        $dishParent = new Dish();
        $mealA = new Meal();
        $mealB = new Meal();

        $dishVariationA->setTitleEn('VariationA');
        $dishVariationB->setTitleEn('VariationB');

        $dishParent->setTitleEn('Parent');

        $dishVariationA->setParent($dishParent);
        $dishVariationB->setParent($dishParent);

        $mealA->setDish($dishVariationA);
        $mealB->setDish($dishVariationB);

        $days->first()->addMeal($mealA);
        $days->first()->addMeal($mealB);

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

            $meal = new Meal();
            $meal->setDish($dish);

            $day->addMeal($meal);
        }

        return $day;
    }
}
