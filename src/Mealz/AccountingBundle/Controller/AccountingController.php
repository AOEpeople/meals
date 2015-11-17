<?php

namespace Mealz\AccountingBundle\Controller;

use Doctrine\ORM\Query;
use Mealz\AccountingBundle\ParticipantList\ParticipantListFactory;
use Mealz\AccountingBundle\Service\Wallet;
use Mealz\MealBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Form\Form;

class AccountingController extends BaseController
{
    /** @var ParticipantListFactory $participantListFactory */
    protected $participantListFactory;

    /** @var Wallet $wallet */
    protected $wallet;

    protected $formView;

    /** @var \DateTime $startDay */
    protected $startDay;

    /** @var \DateTime $endDay */
    protected $endDay;

    public function listAction(Request $request)
    {
        $this->initialize($request);

        if ($this->getDoorman()->isKitchenStaff()) {
            return $this->listForKitchenStaffAction();
        } elseif ($this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->listForIndividualAction();
        } else {
            throw new AccessDeniedException();
        }
    }

    public function listForKitchenStaffAction()
    {
        $participantList = $this->participantListFactory->getList($this->startDay, $this->endDay);

        return $this->render('MealzAccountingBundle:Accounting:list_kitchen.html.twig', array(
            'wallet' => $this->wallet,
            'startDay' => $this->startDay,
            'endDay' => $this->endDay,
            'participantList' => $participantList,
            'timePeriodForm' => $this->formView
        ));
    }

    public function listForIndividualAction()
    {
        $profile = $this->getProfile();

        $participantList = $this->participantListFactory->getList($this->startDay, $this->endDay, $profile);

        return $this->render('MealzAccountingBundle:Accounting:list_individual.html.twig', array(
            'walletBalance' => $this->wallet->getBalance($profile),
            'startDay' => $this->startDay,
            'endDay' => $this->endDay,
            'participations' => $participantList->getParticipations($profile),
            'countAccountableParticipations' => $participantList->countAccountableParticipations($profile),
            'timePeriodForm' => $this->formView
        ));
    }

    /**
     * Initialize everything, which is used by both list actions
     *
     * @param Request $request
     */
    private function initialize(Request $request)
    {
        $this->participantListFactory = $this->get('mealz_accounting.participant_list_factory');
        $this->wallet = $this->get('mealz_accounting.wallet');

        $form = $this->generateTimePeriodForm();
        $this->formView = $form->createView();

        // handle form submission
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $this->startDay = $form->get('from')->getData();
                $this->endDay = $form->get('to')->getData();
            }
        } else {
            $this->startDay = new \DateTime('first day of last month');
            $this->endDay = new \DateTime('last day of last month');
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