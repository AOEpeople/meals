<?php

namespace Mealz\AccountingBundle\Controller;

use Mealz\MealBundle\Controller\BaseController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class WalletController extends BaseController
{
    public function showAction()
    {
        if ($this->get('security.context')->isGranted('ROLE_USER') &&
            false === $this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
                $wallet = $this->get('mealz_accounting.wallet');
                return $this->render('MealzAccountingBundle:Accounting:wallet_individual.html.twig', array(
                    'walletBalance' => $wallet->getBalance($this->getProfile())
                ));

        } else {
            throw new AccessDeniedException();
        }
    }
}