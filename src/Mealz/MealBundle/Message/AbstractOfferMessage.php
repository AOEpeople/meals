<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Message;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Service\Notification\MessageInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

abstract class AbstractOfferMessage implements MessageInterface
{
    protected TranslatorInterface $translator;

    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    protected function getBookedDishTitle(Participant $participant): string
    {
        $bookedDish = $participant->getMeal()->getDish();
        $dishTitle = $bookedDish->getTitleEn();

        if ($bookedDish->isCombinedDish()) {
            /** @var Dish $dish */
            foreach ($participant->getCombinedDishes() as $dish) {
                $dishTitle .= ' - ' . $dish->getTitleEn();
            }

            return $dishTitle;
        }

        $parentDish = $bookedDish->getParent();
        if (null === $parentDish) {     // i.e. simple dish
            return $dishTitle;
        }

        // booked dish is a variation, return parent dish title concatenated with variation title
        return $parentDish->getTitleEn() . ' ' . $dishTitle;
    }
}
