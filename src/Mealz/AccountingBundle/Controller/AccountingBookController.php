<?php

declare(strict_types=1);

namespace App\Mealz\AccountingBundle\Controller;

use App\Mealz\MealBundle\Controller\BaseController;
use DateTime;
use Exception;
use Qipsius\TCPDFBundle\Controller\TCPDFController;
use ReflectionException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("is_granted('ROLE_FINANCE')")
 */
class AccountingBookController extends BaseController
{
    public function list(): Response
    {
        // Get first and last day of previous month
        $minDateFirst = new DateTime('first day of previous month');
        $minDateFirst->setTime(0, 0);
        $maxDateFirst = new DateTime('last day of previous month');
        $maxDateFirst->setTime(23, 59, 59);

        // Get first and last day of actual month
        $minDate = new DateTime('first day of this month');
        $minDate->setTime(0, 0);
        $maxDate = new DateTime('today');
        $maxDate->setTime(23, 59, 59);

        // Create headline for twig template
        $headingFirst = $minDateFirst->format('d.m.-') . $maxDateFirst->format('d.m.Y');
        $heading = $minDate->format('d.m.-') . $maxDate->format('d.m.Y');

        // Get array of users with their amount of transactions in previous month
        $transactionRepo = $this->getTransactionRepository();
        $usersFirst = $transactionRepo->findUserDataAndTransactionAmountForGivenPeriod($minDateFirst, $maxDateFirst);

        // Get array of users with their amount of transactions in actual month
        $users = $transactionRepo->findUserDataAndTransactionAmountForGivenPeriod($minDate, $maxDate);

        return $this->render('MealzAccountingBundle:Accounting/Admin:accountingBook.html.twig', [
            'headingFirst' => $headingFirst,
            'heading' => $heading,
            'usersFirst' => $usersFirst,
            'users' => $users,
        ]);
    }

    /**
     * List all transactions that were payed by cash on the finances page.
     *
     * @throws Exception
     */
    public function listAllTransactions(?string $dateRange): Response
    {
        $headingFirst = null;
        $transactionsFirst = null;
        $minDateFirst = null;
        $maxDateFirst = null;

        if (null === $dateRange) {
            // Get first and last day of previous month
            $minDateFirst = new DateTime('first day of previous month');
            $minDateFirst->setTime(0, 0);
            $maxDateFirst = new DateTime('last day of previous month');
            $maxDateFirst->setTime(23, 59, 59);

            // Create headline for twig template
            $headingFirst = $minDateFirst->format('d.m.') . ' - ' . $maxDateFirst->format('d.m.Y');

            // Get first and last day of actual month
            $minDate = new DateTime('first day of this month');
            $maxDate = new DateTime('today');
            $minDate->setTime(0, 0);
            $maxDate->setTime(23, 59, 59);

            $transactionsFirst = $this->getTransactionRepository()->findAllTransactionsInDateRange($minDateFirst, $maxDateFirst);
        } else {
            // Get date range set with date range picker by user
            $dateRangeArray = explode('&', $dateRange);
            $minDate = new DateTime($dateRangeArray[0]);
            $maxDate = new DateTime($dateRangeArray[1]);
        }

        $heading = $minDate->format('d.m.') . ' - ' . $maxDate->format('d.m.Y');

        $transactions = $this->getTransactionRepository()->findAllTransactionsInDateRange($minDate, $maxDate);

        return $this->render('MealzAccountingBundle:Accounting/Finance:finances.html.twig', [
            'headingFirst' => $headingFirst,
            'heading' => $heading,
            'transactionsFirst' => $transactionsFirst,
            'transactions' => $transactions,
            'minDate' => (null === $minDateFirst) ? $minDate->format('m/d/Y') : $minDateFirst->format('m/d/Y'),
            'maxDate' => (null === $maxDateFirst) ? $maxDate->format('m/d/Y') : $maxDateFirst->format('m/d/Y'),
        ]);
    }

    /**
     * Export transaction table as PDF for finance staff.
     *
     * @throws ReflectionException
     * @throws Exception
     */
    public function exportPDF(?string $dateRange, TCPDFController $pdfGen): Response
    {
        // Get date range set with date range picker by user
        $dateRange = str_replace('-', '/', $dateRange);
        $dateRangeArray = explode('&', $dateRange);
        $minDate = new DateTime($dateRangeArray[0]);
        $maxDate = new DateTime($dateRangeArray[1]);

        $heading = $minDate->format('d.m.') . ' - ' . $maxDate->format('d.m.Y');
        $transactionRepo = $this->getTransactionRepository();
        $transactions = $transactionRepo->findAllTransactionsInDateRange($minDate, $maxDate);

        // Create PDF file
        $pdf = $pdfGen->create(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->setHeaderData('', 0, '', '', [0, 0, 0], [255, 255, 255]);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();

        $filename = $this->get('translator')->trans('payment.transaction_history.finances.pdf') . '-' . $minDate->format('d.m.Y') . '-' . $maxDate->format('d.m.Y');
        $pdf->SetTitle($filename);

        $cssFile = file_get_contents(__DIR__ . '/../Resources/css/transaction-export.css');

        $includeCSS = '<style>' . $cssFile . '</style>';

        $html = $this->renderView('MealzAccountingBundle:Accounting/Finance:print_finances.html.twig', [
            'headingFirst' => null,
            'heading' => $heading,
            'transactionsFirst' => null,
            'transactions' => $transactions,
            'minDate' => $minDate->format('m/d/Y'),
            'maxDate' => $maxDate->format('m/d/Y'),
        ]);

        $pdf->writeHTML($includeCSS . $html);

        $content = $pdf->Output($filename . '.pdf', 'S');
        $now = gmdate('D, d M Y H:i:s') . ' GMT';

        return new Response($content, 200, [
            'Content-Type' => 'application/pdf',
            'Expires' => $now,
            'Last-Modified' => $now,
            'Content-Disposition' => 'inline; filename="' . basename($filename) . '"',
        ]);
    }
}
