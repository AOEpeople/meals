<?php

namespace App\Mealz\AccountingBundle\Controller;

use App\Mealz\AccountingBundle\Service\Wallet;
use App\Mealz\MealBundle\Controller\BaseController;
use Symfony\Component\Form\Form;
use Symfony\Component\HttpFoundation\Response;

class AccountingUserController extends BaseController
{
    public function indexAction(): Response
    {
        return $this->render('MealzAccountingBundle:Accounting/User:index.html.twig', [
            'participations' => $this->getParticipantRepository()->getLastAccountableParticipations($this->getProfile(), 5),
            'transactions' => $this->getTransactionRepository()->getLastSuccessfulTransactions($this->getProfile(), 3),
            'walletBalance' => $this->getWallet()->getBalance($this->getProfile()),
            'goForm' => $this->getDoorman()->isKitchenStaff() ? $this->generateGoActionForm()->createView() : null,
        ]);
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
            ->add('profile', 'entity', [
                'class' => 'MealzUserBundle:Profile',
                'label' => false, ])
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
}
