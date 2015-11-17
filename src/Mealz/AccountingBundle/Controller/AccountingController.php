<?php

namespace Mealz\AccountingBundle\Controller;

use Doctrine\ORM\Query;
use Mealz\AccountingBundle\Entity\Transaction;
use Mealz\AccountingBundle\Form\CashPaymentAdminForm;
use Mealz\AccountingBundle\ParticipantList\ParticipantListFactory;
use Mealz\AccountingBundle\Service\Wallet;
use Mealz\MealBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccountingController extends BaseController
{

    public function listAction(Request $request)
    {
        if ($this->getDoorman()->isKitchenStaff()) {
            return $this->listForKitchenStaffAction($request);
        } elseif ($this->get('security.context')->isGranted('ROLE_USER')) {
            return $this->listForIndividualAction();
        } else {
            throw new AccessDeniedException();
        }
    }

    public function listForKitchenStaffAction($request)
    {
        /** @var ParticipantListFactory $participantListFactory */
        $participantListFactory = $this->get('mealz_accounting.participant_list_factory');

        /** @var Wallet $wallet */
        $wallet = $this->get('mealz_accounting.wallet');

        $startDay = new \DateTime('first day of last month');
        $endDay = new \DateTime('last day of last month');

        $participantList = $participantListFactory->getList($startDay, $endDay);

        $transaction = new Transaction();
        $transaction->setId(uniqid('BAR-'));
        $transaction->setSuccessful();

        $form = $this->createForm(new CashPaymentAdminForm(), $transaction);

        // handle form submission
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($transaction);
                $em->flush();

                $this->addFlashMessage('Cash payment has been added.', 'notice');

                return $this->redirectToRoute('MealzAccountingBundle_Accounting');
            }
        }

        return $this->render('MealzAccountingBundle:Accounting:list_kitchen.html.twig', array(
            'wallet' => $wallet,
            'startDay' => $startDay,
            'endDay' => $endDay,
            'participantList' => $participantList,
            'form' => $form->createView()
        ));
    }

    public function listForIndividualAction()
    {
        /** @var ParticipantListFactory $participantListFactory */
        $participantListFactory = $this->get('mealz_accounting.participant_list_factory');

        /** @var Wallet $wallet */
        $wallet = $this->get('mealz_accounting.wallet');

        $startDay = new \DateTime('first day of last month');
        $endDay = new \DateTime('last day of last month');

        $profile = $this->getProfile();

        $participantList = $participantListFactory->getList($startDay, $endDay, $profile);

        return $this->render('MealzAccountingBundle:Accounting:list_individual.html.twig', array(
            'walletBalance' => $wallet->getBalance($profile),
            'startDay' => $startDay,
            'endDay' => $endDay,
            'participations' => $participantList->getParticipations($profile),
            'countAccountableParticipations' => $participantList->countAccountableParticipations($profile)
        ));
    }

}