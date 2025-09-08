<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Message;

use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Service\OfferService;
use Override;
use Symfony\Contracts\Translation\TranslatorInterface;

final class OfferAcceptedMessage extends AbstractOfferMessage
{
    private Participant $participant;
    private OfferService $offerService;

    public function __construct(Participant $participant, OfferService $offerService, TranslatorInterface $translator)
    {
        parent::__construct($translator);

        $this->participant = $participant;
        $this->offerService = $offerService;
    }

    #[Override]
    public function getContent(): string
    {
        return $this->translator->trans(
            'mattermost.offer_taken',
            [
                '%count%' => $this->offerService->getOfferCount($this->participant->getMeal()->getDateTime()),
                '%takenOffer%' => $this->getBookedDishTitle($this->participant),
            ],
            'messages',
            'en_EN'
        );
    }
}
