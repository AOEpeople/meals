<?php

namespace Mealz\AccountingBundle\Controller\Payment;

use Mealz\MealBundle\Controller\BaseController;
use Mealz\AccountingBundle\Entity\Transaction;
use Symfony\Component\HttpFoundation\Request;
use Mealz\AccountingBundle\Form\CashPaymentAdminForm;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class CashController extends BaseController
{
    public function createPaymentAction($profile)
    {
        if (!$this->getDoorman()->isKitchenStaff()) {
            throw new AccessDeniedException();
        }
        $request = $this->get('request');

        $profileEntity = $this->getDoctrine()
            ->getRepository('MealzUserBundle:Profile')
            ->find($profile);

        $transaction = new Transaction();
        $transaction->setId(uniqid('BAR-'));
        $transaction->setUser($profileEntity);
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

                return $this->redirectToRoute('MealzAccountingBundle_Accounting_Admin', ['profile' => $profile]);
            }
        }

        return $this->render('MealzAccountingBundle:Accounting\\partials:form_payment_cash.html.twig', array(
            'form' => $form->createView()
        ));
    }
}