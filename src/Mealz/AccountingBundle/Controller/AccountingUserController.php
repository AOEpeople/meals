<?php

namespace Mealz\AccountingBundle\Controller;

use DateTime;
use Doctrine\ORM\Query;
use Mealz\AccountingBundle\Entity\TransactionRepository;
use Mealz\AccountingBundle\Service\Wallet;
use Mealz\MealBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Form;

class AccountingUserController extends BaseController
{
    public function indexAction()
    {
        return $this->render('MealzAccountingBundle:Accounting/User:index.html.twig', array(
            'participations' => $this->getParticipantRepository()->getLastAccountableParticipations($this->getProfile(), 5),
            'transactions' => $this->getTransactionRepository()->getLastSuccessfulTransactions($this->getProfile(), 3),
            'walletBalance' => $this->getWallet()->getBalance($this->getProfile()),
            'goForm' => $this->getDoorman()->isKitchenStaff() ? $this->generateGoActionForm()->createView() : null,
        ));
    }

    public function listParticipationAction(Request $request)
    {
        $form = $this->generateTimePeriodForm();
        $formView = $form->createView();

        $startDay = new DateTime('-1 month 00:00:00');
        $endDay = new DateTime('now');

        // handle form submission
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $startDay = $form->get('from')->getData();
                $endDay = $form->get('to')->getData();
            }
        }

        return $this->render('MealzAccountingBundle:Accounting/User:list_participation.html.twig', array(
            'startDay' => $startDay,
            'endDay' => $endDay,
            'participations' => $this->getParticipantRepository()->getParticipantsOnDays($startDay, $endDay, $this->getProfile()),
            'timePeriodForm' => $formView,
        ));
    }

    public function listTransactionAction(Request $request)
    {
        $form = $this->generateTimePeriodForm();
        $formView = $form->createView();

        $startDay = new DateTime('-6 months 00:00:00');
        $endDay = new DateTime('+1 month');

        // handle form submission
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $startDay = $form->get('from')->getData();
                $endDay = $form->get('to')->getData();
            }
        }

        $endDay->setTime(23, 59, 59);

        return $this->render('MealzAccountingBundle:Accounting/User:list_transaction.html.twig', array(
            'startDay' => $startDay,
            'endDay' => $endDay,
            'transactions' => $this->getTransactionRepository()->getSuccessfulTransactionsOnDays($startDay, $endDay, $this->getProfile()),
            'timePeriodForm' => $formView,
        ));
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

    /**
     * @return Form
     */
    private function generateGoActionForm()
    {
        return $this->container->get('form.factory')->createNamedBuilder(null, 'form', null, [
            'method' => 'GET',
            'action' => $this->generateUrl('MealzAccountingBundle_Accounting_Admin_go'),
            'csrf_protection' => false,
        ])
            ->add('profile', 'entity', array(
                'class' => 'MealzUserBundle:Profile',
                'label' => false))
            ->add('details', 'submit')
            ->getForm();
    }

    /**
     * @return Wallet
     */
    private function getWallet()
    {
        return $this->get('mealz_accounting.wallet');
    }

    /**
     * @return TransactionRepository
     */
    public function getTransactionRepository()
    {
        return $this->getDoctrine()->getRepository('MealzAccountingBundle:Transaction');
    }
}
