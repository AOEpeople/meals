<?php

namespace App\Mealz\AccountingBundle\Controller;

use App\Mealz\AccountingBundle\Service\Wallet;
use App\Mealz\MealBundle\Controller\BaseController;
use App\Mealz\UserBundle\Entity\Profile;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AccountingAdminController extends BaseController
{
    public function goAction(Request $request): RedirectResponse
    {
        $this->assureKitchenStaff();

        $profileId = $request->query->get('profile');

        return $this->redirect($this->generateUrl('MealzAccountingBundle_Accounting_Admin', ['profile' => $profileId]));
    }

    public function indexAction($profileId): Response
    {
        $this->assureKitchenStaff();
        $profile = $this->getProfileById($profileId);

        return $this->render('MealzAccountingBundle:Accounting/Admin:index.html.twig', [
            'profile' => $profile,
            'participations' => $this->getParticipantRepository()->getLastAccountableParticipations($profile, 5),
            'transactions' => $this->getTransactionRepository()->getLastSuccessfulTransactions($profile, 3),
            'walletBalance' => $this->getWallet()->getBalance($profile),
        ]);
    }

    /**
     * @return Wallet
     */
    private function getWallet()
    {
        return $this->get('mealz_accounting.wallet');
    }

    private function getProfileById(string $profileId): Profile
    {
        try {
            return $this->getDoctrine()->getManager()->find(Profile::class, $profileId);
        } catch (EntityNotFoundException $e) {
            throw new NotFoundHttpException(sprintf('Profile with id %s was not found.', $profileId), $e);
        }
    }

    private function assureKitchenStaff(): void
    {
        if (!$this->getDoorman()->isKitchenStaff()) {
            throw new AccessDeniedException();
        }
    }
}
