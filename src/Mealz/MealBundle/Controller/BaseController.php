<?php


namespace Mealz\MealBundle\Controller;


use Mealz\AccountingBundle\Entity\TransactionRepository;
use Mealz\MealBundle\Entity\CategoryRepository;
use Mealz\MealBundle\Entity\DishRepository;
use Mealz\MealBundle\Entity\MealRepository;
use Mealz\MealBundle\Entity\Participant;
use Mealz\MealBundle\Entity\ParticipantRepository;
use Mealz\MealBundle\Service\Doorman;
use Mealz\MealBundle\Service\Link;
use Mealz\UserBundle\Entity\Profile;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Translation\Translator;
use Symfony\Component\VarDumper\VarDumper;


/**
 * Class BaseController
 * @package Mealz\MealBundle\Controller
 */
abstract class BaseController extends Controller
{
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

    /**
     * @return Profile|null
     */
    protected function getProfile()
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
     * @param $message
     * @param $severity "danger", "warning", "info", "success"
     */
    public function addFlashMessage($message, $severity)
    {
        $this->get('session')->getFlashBag()->add($severity, $message);
    }

    protected function ajaxSessionExpiredRedirect()
    {
        $message = $this->get('translator')->trans('session.expired', [], 'messages');
        $this->addFlashMessage($message, 'info');
        $response = array(
            'redirect' => $this->generateUrl('MealzUserBundle_login'),
        );

        return new JsonResponse($response);
    }

    /**
     * @param Participant $participant
     * @param String $takenOffer
     */
    public function sendMail(Participant $participant, $takenOffer)
    {
        $translator = new Translator('en_EN');

        $to = $participant->getProfile()->getUsername() . $translator->trans('mail.domain', array(), 'messages');
        $subject = $translator->trans('mail.subject', array(), 'messages');
        $header = $translator->trans('mail.sender', array(), 'messages');
        $firstname = $participant->getProfile()->getFirstname();

        $message = $translator->trans('mail.message', array(
            '%firstname%' => $firstname,
            '%takenOffer%' => $takenOffer),
            'messages'
        );

        mail($to, $subject, $message, $header);
    }
}