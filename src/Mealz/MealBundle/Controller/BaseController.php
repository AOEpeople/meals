<?php


namespace Mealz\MealBundle\Controller;


use Mealz\AccountingBundle\Entity\TransactionRepository;
use Mealz\MealBundle\Entity\CategoryRepository;
use Mealz\MealBundle\Entity\DishRepository;
use Mealz\MealBundle\Entity\MealRepository;
use Mealz\MealBundle\Entity\ParticipantRepository;
use Mealz\MealBundle\Service\Doorman;
use Mealz\MealBundle\Service\Link;
use Mealz\UserBundle\Entity\Profile;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\EventListener\ValidateRequestListener;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\VarDumper\VarDumper;

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

    public static function slack($message)
    {
        $data = "payload=" . json_encode(array(
                "text" => $message,
                "username" => 'Chef',
                "icon_url" => 'https://www2.pic-upload.de/img/33991182/chef_bot.png', //TODO for Raza: adapt url to image's path on live server
            ));

        $ch = curl_init("https://messages.aoe.com/hooks/f9nb161oppro8nin15zhtrgqqh");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);
        return $result;
    }

    public function countMeals(\DateTime $dateTime)
    {
        $lunchDay = strtotime(date('d-m-Y', date_timestamp_get($dateTime)));
        $lunchTime = strtotime(date('H:i:s', date_timestamp_get($dateTime)));

        $nowTime = strtotime(date('H:i:s', time()));
        $nowDay = strtotime(date('d-m-Y', time()));


        $todayMin = new \DateTime('today 00:00');
        $todayMax = new \DateTime('today 23:59');
        $tomorrowMin = new \DateTime('tomorrow 00:00');
        $tomorrowMax = new \DateTime('tomorrow 23:59');
        $i = 0;

        if ($nowTime < $lunchTime and $nowDay === $lunchDay) {
            $participants = $this->getParticipantRepository()->getParticipantsOnDays($todayMin, $todayMax);
            foreach ($participants as $participant) {
                if ($participant->isPending()) {
                    $i++;
                }
            }
            return $i;
        } else {
            $participants = $this->getParticipantRepository()->getParticipantsOnDays($tomorrowMin, $tomorrowMax);
            foreach ($participants as $participant) {
                if ($participant->isPending()) {
                    $i++;
                }
            }
            return $i;
        }



    }
}