<?php

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\AccountingBundle\Entity\TransactionRepository;
use App\Mealz\MealBundle\Entity\CategoryRepository;
use App\Mealz\MealBundle\Entity\DishRepository;
use App\Mealz\MealBundle\Entity\MealRepository;
use App\Mealz\MealBundle\Entity\Participant;
use App\Mealz\MealBundle\Entity\ParticipantRepository;
use App\Mealz\MealBundle\Service\Doorman;
use App\Mealz\MealBundle\Service\Link;
use App\Mealz\UserBundle\Entity\Profile;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Class BaseController
 * @package Mealz\MealBundle\Controller
 */
abstract class BaseController extends AbstractController
{
    public static function getSubscribedServices(): array
    {
        $services = parent::getSubscribedServices();
        $services['mealz_meal.doorman'] = '?'.Doorman::class;
        $services['monolog.logger.balance'] = '?'.LoggerInterface::class;
        $services['translator'] = '?'.TranslatorInterface::class;

        return $services;
    }

    /**
     * @return MealRepository
     */
    public function getMealRepository()
    {
        return $this->getDoctrine()->getRepository('MealzMealBundle:Meal');
    }

    /**
     * @return DishRepository
     */
    public function getDishRepository()
    {
        return $this->getDoctrine()->getRepository('MealzMealBundle:Dish');
    }

    /**
     * @return ParticipantRepository
     */
    public function getParticipantRepository()
    {
        return $this->getDoctrine()->getRepository('MealzMealBundle:Participant');
    }

    /**
     * @return CategoryRepository
     */
    public function getCategoryRepository()
    {
        return $this->getDoctrine()->getRepository('MealzMealBundle:Category');
    }

    /**
     * @return TransactionRepository
     */
    public function getTransactionRepository()
    {
        return $this->getDoctrine()->getRepository('MealzAccountingBundle:Transaction');
    }

    /**
     * @return Doorman
     */
    protected function getDoorman()
    {
        return $this->get('mealz_meal.doorman');
    }

    protected function getProfile(): ?Profile
    {
        return $this->getUser() ? $this->getUser()->getProfile() : null;
    }

    /**
     * @param $object
     * @param null $action
     * @param bool $referenceType
     * @return string
     */
    public function generateUrlTo($object, $action = null, $referenceType = UrlGeneratorInterface::ABSOLUTE_PATH)
    {
        /** @var Link $linkService */
        $linkService = $this->get('mealz_meal.link');

        return $linkService->link($object, $action, $referenceType);
    }

    /**
     * @param mixed  $message
     * @param string $severity "danger", "warning", "info", "success"
     */
    public function addFlashMessage($message, string $severity): void
    {
        $this->get('session')->getFlashBag()->add($severity, $message);
    }

    protected function ajaxSessionExpiredRedirect(): JsonResponse
    {
        $message = $this->get('translator')->trans('session.expired', [], 'messages');
        $this->addFlashMessage($message, 'info');
        $response = [
            'redirect' => $this->generateUrl('MealzUserBundle_login'),
        ];

        return new JsonResponse($response);
    }

    public function sendMail(Participant $participant, string $takenOffer): void
    {
        $translator = $this->get('translator');

        $recipient = $participant->getProfile()->getUsername() . $translator->trans('mail.domain', array(), 'messages');
        $subject = $translator->trans('mail.subject', [], 'messages');
        $header = $translator->trans('mail.sender', [], 'messages');
        $firstname = $participant->getProfile()->getFirstname();

        $message = $translator->trans(
            'mail.message',
            [
                '%firstname%' => $firstname,
                '%takenOffer%' => $takenOffer
            ],
            'messages'
        );

        mail($recipient, $subject, $message, $header);
    }
}
