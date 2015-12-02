<?php

namespace Mealz\AccountingBundle\Controller;

use Doctrine\ORM\Query;
use Mealz\AccountingBundle\ParticipantList\ParticipantListFactory;
use Mealz\AccountingBundle\Service\Wallet;
use Mealz\MealBundle\Controller\BaseController;
use Mealz\UserBundle\Entity\Profile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Form\Form;

class AccountingController extends BaseController
{
    /** @var Wallet $wallet */
    protected $wallet;

    public function listAction()
    {
        $this->wallet = $this->get('mealz_accounting.wallet');

        if ($this->getDoorman()->isKitchenStaff()) {
            return $this->listForKitchenStaffAction();
        } elseif ($this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->listForIndividualAction($this->getProfile());
        } else {
            throw new AccessDeniedException();
        }
    }

    public function listForKitchenStaffAction()
    {
        $profiles = $this->getDoctrine()
            ->getRepository('MealzUserBundle:Profile')
            ->findAll();

        return $this->render('MealzAccountingBundle:Accounting:list_kitchen.html.twig', array(
            'profiles' => $profiles,
            'wallet' => $this->wallet
        ));
    }

    public function listForIndividualAction(Profile $profile)
    {
        $request = $this->get('request');

        $form = $this->generateTimePeriodForm();
        $formView = $form->createView();

        // handle form submission
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $startDay = $form->get('from')->getData();
                $endDay = $form->get('to')->getData();
            }
        } else {
            $startDay = new \DateTime('first day of last month');
            $endDay = new \DateTime('last day of last month');
        }

        $participantListFactory = $this->get('mealz_accounting.participant_list_factory');
        $participantList = $participantListFactory->getList($startDay, $endDay, $profile);

        return $this->render('MealzAccountingBundle:Accounting:list_individual.html.twig', array(
            'startDay' => $startDay,
            'endDay' => $endDay,
            'participations' => $participantList->getParticipations($profile),
            'countAccountableParticipations' => $participantList->countAccountableParticipations($profile),
            'timePeriodForm' => $formView
        ));
    }

    public function detailForKitchenStaffAction($profile)
    {
        if ($this->getDoorman()->isKitchenStaff()) {
            $profileEntity = $this->getDoctrine()
                ->getRepository('MealzUserBundle:Profile')
                ->find($profile);

            return $this->listForIndividualAction($profileEntity);
        } else {
            throw new AccessDeniedException();
        }
    }

    /**
     * Generate a form where you can select a time period
     * for the participation list
     *
     * @return Form
     */
    private function generateTimePeriodForm()
    {
        return $this->createFormBuilder()
            ->add('from', 'date', array('widget' => 'single_text'))
            ->add('to', 'date', array('widget' => 'single_text'))
            ->add('send', 'submit')
            ->getForm();
    }
}