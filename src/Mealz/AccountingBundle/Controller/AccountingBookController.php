<?php

namespace Mealz\AccountingBundle\Controller;

use Mealz\MealBundle\Controller\BaseController;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class AccountingBookController
 * @package Mealz\AccountingBundle\Controller
 */
class AccountingBookController extends BaseController
{
    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        // Deny access for unprivileged (non-admin) users
        if (!$this->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        // Get first and last day of previous month
        $minDateFirst = new \DateTime('first day of previous month');
        $minDateFirst->setTime(0, 0, 0);
        $maxDateFirst = new \DateTime('last day of previous month');
        $maxDateFirst->setTime(23, 59, 59);

        // Get first and last day of actual month
        $minDate = new \DateTime('first day of this month');
        $minDate->setTime(0, 0, 0);
        $maxDate = new \DateTime('today');
        $maxDate->setTime(23, 59, 59);

        // Create headline for twig template
        $headingFirst = $minDateFirst->format('d.m.-') . $maxDateFirst->format('d.m.Y');
        $heading = $minDate->format('d.m.-') . $maxDate->format('d.m.Y');

        // Get array of users with their amount of transactions in previous month
        $transactionRepository = $this->getTransactionRepository();
        $usersFirst = $transactionRepository->findUserDataAndTransactionAmountForGivenPeriod($minDateFirst, $maxDateFirst);

        // Get array of users with their amount of transactions in actual month
        $users = $transactionRepository->findUserDataAndTransactionAmountForGivenPeriod($minDate, $maxDate);

        return $this->render('MealzAccountingBundle:Accounting\\Admin:accountingBook.html.twig', array(
            'headingFirst' => $headingFirst,
            'heading' => $heading,
            'usersFirst' => $usersFirst,
            'users' => $users,

        ));
    }

    /**
     * List all transactions that were payed by cash on the finances page
     * @param $dateRange
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function listAllTransactionsAction($dateRange)
    {
        if (!$this->isGranted('ROLE_FINANCE')) {
            throw new AccessDeniedException();
        }

        $headingFirst = null;
        $transactionsFirst = null;
        $minDateFirst = null;
        $maxDateFirst = null;

        if ($dateRange === null) {
            // Get first and last day of previous month
            $minDateFirst = new \DateTime('first day of previous month');
            $minDateFirst->setTime(0, 0, 0);
            $maxDateFirst = new \DateTime('last day of previous month');
            $maxDateFirst->setTime(23, 59, 59);

            // Create headline for twig template
            $headingFirst = $minDateFirst->format('d.m.') . ' - ' . $maxDateFirst->format('d.m.Y');

            // Get first and last day of actual month
            $minDate = new \DateTime('first day of this month');
            $maxDate = new \DateTime('today');
            $minDate->setTime(0, 0, 0);
            $maxDate->setTime(23, 59, 59);

            $transactionsFirst = $this->getTransactionRepository()->findAllTransactionsInDateRange($minDateFirst, $maxDateFirst);
        } else {
            // Get date range set with date range picker by user
            $dateRangeArray = explode('&', $dateRange);
            $minDate = new \DateTime($dateRangeArray[0]);
            $maxDate = new \DateTime($dateRangeArray[1]);
        }

        $heading = $minDate->format('d.m.') . ' - ' . $maxDate->format('d.m.Y');

        $transactions = $this->getTransactionRepository()->findAllTransactionsInDateRange($minDate, $maxDate);

        return $this->render('MealzAccountingBundle:Accounting/Finance:finances.html.twig', array(
            'headingFirst' => $headingFirst,
            'heading' => $heading,
            'transactionsFirst' => $transactionsFirst,
            'transactions' => $transactions,
            'minDate' => ($minDateFirst === null) ? $minDate->format('m/d/Y') : $minDateFirst->format('m/d/Y'),
            'maxDate' => ($maxDateFirst === null) ? $maxDate->format('m/d/Y') : $maxDateFirst->format('m/d/Y'),
        ));
    }

    /**
     * Export transaction table as PDF for finance staff
     * @param $dateRange
     * @return string
     * @throws \Exception
     */
    public function exportPDFAction($dateRange)
    {
        if (!$this->isGranted('ROLE_FINANCE')) {
            throw new AccessDeniedException();
        }

        // Get date range set with date range picker by user
        $dateRange = str_replace('-', '/', $dateRange);
        $dateRangeArray = explode('&', $dateRange);
        $minDate = new \DateTime($dateRangeArray[0]);
        $maxDate = new \DateTime($dateRangeArray[1]);

        $heading = $minDate->format('d.m.') . ' - ' . $maxDate->format('d.m.Y');
        $transactionRepository = $this->getTransactionRepository();
        $transactions = $transactionRepository->findAllTransactionsInDateRange($minDate, $maxDate);

        // Create PDF file
        $pdf = $this->get('white_october.tcpdf')->create(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->setHeaderData('', 0, '', '', array(0, 0, 0), array(255, 255, 255));
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();

        $filename = $this->get('translator')->trans('payment.transaction_history.finances.pdf') . '-' . $minDate->format('d.m.Y') . '-' . $maxDate->format('d.m.Y');
        $pdf->SetTitle($filename);

        $cssFile = file_get_contents($this->getParameter('env_url') . '/media/transaction-export.css');

        $includeCSS = '<style>' . $cssFile . '</style>';

        $html = $this->renderView('MealzAccountingBundle:Accounting/Finance:print_finances.html.twig', array(
            'headingFirst' => null,
            'heading' => $heading,
            'transactionsFirst' => null,
            'transactions' => $transactions,
            'minDate' => $minDate->format('m/d/Y'),
            'maxDate' => $maxDate->format('m/d/Y'),
        ));

        $pdf->writeHTML($includeCSS . $html);
        return $pdf->Output($filename . '.pdf', 'I');
    }
}