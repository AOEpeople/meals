<?php

namespace App\Mealz\MealBundle\Twig\Extension;

use App\Mealz\MealBundle\Service\Link as LinkService;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

class Link extends AbstractExtension
{
    protected LinkService $linkService;

    public function __construct(LinkService $linkService)
    {
        $this->linkService = $linkService;
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('link', [$this, 'link']),
            new TwigFunction('linkEvent', [$this, 'linkEvent']),
            new TwigFunction('linkEventParticipant', [$this, 'linkEventParticipant']),
        ];
    }

    public function link($object, $action = null, $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        return $this->linkService->link($object, $action, $referenceType);
    }

    public function linkEventParticipant($object, $action = null, $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        return $this->linkService->linkEventParticipant($object, $action, $referenceType);
    }

    public function linkEvent($object, $day, $action = null, $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH): string
    {
        return $this->linkService->linkEvent($object, $day, $action, $referenceType);
    }
}
