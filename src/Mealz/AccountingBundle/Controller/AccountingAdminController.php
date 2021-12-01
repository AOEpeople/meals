<?php

namespace App\Mealz\AccountingBundle\Controller;

use App\Mealz\AccountingBundle\Service\Wallet;
use App\Mealz\MealBundle\Controller\BaseController;
use App\Mealz\UserBundle\Entity\Profile;
use DateTime;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\Form\Form;
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

    public function listParticipationAction($profileId, Request $request): Response
    {
        $this->assureKitchenStaff();
        $profile = $this->getProfileById($profileId);
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

        return $this->render('MealzAccountingBundle:Accounting/Admin:list_participation.html.twig', [
            'profile' => $profile,
            'startDay' => $startDay,
            'endDay' => $endDay,
            'participations' => $this->getParticipantRepository()->getParticipantsOnDays($startDay, $endDay, $profile),
            'timePeriodForm' => $formView,
        ]);
    }

    public function listTransactionAction($profileId, Request $request): Response
    {
        $this->assureKitchenStaff();
        $profile = $this->getProfileById($profileId);

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

        return $this->render('MealzAccountingBundle:Accounting/Admin:list_transaction.html.twig', [
            'profile' => $profile,
            'startDay' => $startDay,
            'endDay' => $endDay,
            'transactions' => $this->getTransactionRepository()->getSuccessfulTransactionsOnDays($startDay, $endDay, $profile),
            'timePeriodForm' => $formView,
        ]);
    }

    /**
     * Generate a form where you can select a time period
     * for the participation list.
     *
     * @return Form
     */
    private function generateTimePeriodForm()
    {
        return $this->createFormBuilder()
            ->add('from', \Symfony\Component\Form\Extension\Core\Type\DateType::class, ['widget' => 'single_text'])
            ->add('to', \Symfony\Component\Form\Extension\Core\Type\DateType::class, ['widget' => 'single_text'])
            ->add('send', \Symfony\Component\Form\Extension\Core\Type\SubmitType::class)
            ->getForm();
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
