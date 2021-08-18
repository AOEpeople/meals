<?php

namespace App\Mealz\AccountingBundle\Tests\Controller\Payment;

use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Translation\Translator;

use App\Mealz\AccountingBundle\Controller\Payment\EcashController;
use App\Mealz\AccountingBundle\DataFixtures\ORM\LoadTransactions;
use App\Mealz\MealBundle\Tests\Controller\AbstractControllerTestCase;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;

class EcashControllerTest extends AbstractControllerTestCase
{
    /**
     * Set up the testing environment
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->createDefaultClient();
        $this->clearAllTables();
        $this->loadFixtures([
            new LoadRoles(),
            // self::$container is a special container that allow access to private services
            // see: https://symfony.com/blog/new-in-symfony-4-1-simpler-service-testing
            new LoadUsers(self::$container->get('security.user_password_encoder.generic')),
            new LoadTransactions()
        ]);
    }

    /**
     * Check if form and PayPal button is rendered correctly
     */
    public function testFormRendering(): void
    {
        $userProfile = $this->getUserProfile();

        // Open home page
        $crawler = $this->client->request('GET', '/');
        $this->assertTrue($this->client->getResponse()->isSuccessful());

        // Login
        $loginForm = $crawler->filterXPath('//form[@name="login-form"]')->form([
            '_username' => $userProfile->getUsername(),
            '_password' => $userProfile->getUsername()
        ]);
        $this->client->followRedirects();
        $crawler = $this->client->submit($loginForm, []);

        // Click on the balance link
        $balanceLink = $crawler->filterXPath('//div[@class="balance-text"]/a')->link();
        $crawler = $this->client->click($balanceLink);

        // Client should be on transaction history page
        $this->assertGreaterThan(
            0,
            $crawler->filterXPath('//div[contains(@class,"transaction-history")]')->count(),
            'Transaction history page not found'
        );

        // Check if "add funds" button exists
        $this->assertGreaterThan(0, $crawler->filterXPath('//*[@id="ecash"]')->count(), 'Add funds button not found');
    }

    /**
     * Test payment form handling and database persistence
     */
    public function testPaymentFormHandlingAction(): void
    {
        // Mock EcashController class
        $ecashController = $this->getMockBuilder(EcashController::class)
            ->setMethods(array(
                'validatePaypalTransaction',
                'get',
                'getDoctrine',
                'generateUrl',
                'addFlashMessage'
            ))
            ->getMock();

        $ecashController->expects($this->atLeastOnce())
            ->method('validatePaypalTransaction')
            ->will($this->returnValue(['statuscode' => 200, 'amount' => '5.23']));

        $ecashController->expects($this->atLeastOnce())
            ->method('get')
            ->with('translator')
            ->will($this->returnValue(new Translator('de')));

        $ecashController->expects($this->atLeastOnce())
            ->method('getDoctrine')
            ->will($this->returnValue($this->getDoctrine()));

        $ecashController->expects($this->atLeastOnce())
            ->method('generateUrl')
            ->will($this->returnValue('/accounting/transactions'));

        // Simulate submit request
        $request = Request::create(
            '',
            'POST',
            array(),
            array(),
            array(),
            array(),
            '[' .
            '{"name":"ecash[profile]","value":"alice"},' .
            '{"name":"ecash[orderid]","value":"52T16708K70721706"},' .
            '{"name":"ecash[amount]","value":"5,23"},' .
            '{"name":"ecash[paymethod]","value":"0"},' .
            '{"name":"ecash[_token]","value":"4xEN3hEBs29aFJRFtucTATjBI-iEjdrot4kdT1hRl18"}]'
        );

        // Create expected response
        $expectedResponse = new Response(
            '/accounting/transactions',
            Response::HTTP_OK,
            array('content-type' => 'text/html')
        );
        $expectedResponse->headers->remove('date');

        $actualResponse = $ecashController->paymentFormHandling($request);
        $actualResponse->headers->remove('date');

        $this->assertEquals($expectedResponse, $actualResponse);

        // Check database entry
        $transactionRepo = $this->getDoctrine()->getRepository('MealzAccountingBundle:Transaction');
        $entry = $transactionRepo->findBy(array('profile' => 'alice'), array('id' => 'DESC'));

        $this->assertEquals('52T16708K70721706', $entry[0]->getOrderId());
        $this->assertEquals(5.23, $entry[0]->getAmount());
    }
}
