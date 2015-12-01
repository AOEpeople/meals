<?php

namespace Mealz\AccountingBundle\Controller\Payment;

use Mealz\MealBundle\Controller\BaseController;
use Mealz\AccountingBundle\Entity\Transaction;
use Symfony\Component\HttpFoundation\Request;
use Mealz\AccountingBundle\Form\CashPaymentAdminForm;

class CashController extends BaseController
{
    public function createPaymentAction(Request $request)
    {
        $transaction = new Transaction();
        $transaction->setId(uniqid('BAR-'));
        $transaction->setSuccessful();

        $form = $this->createForm(new CashPaymentAdminForm(), $transaction, array(
            'action' => $this->generateUrl('mealz_accounting_payment_cash')
        ));

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

        return $this->render('MealzAccountingBundle:Accounting:payment_cash.html.twig', array(
            'form' => $form->createView()
        ));
    }
}