<?php

namespace Mealz\MealBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use Mealz\MealBundle\Entity\DishVariation;
use Symfony\Component\DomCrawler\Crawler;

use Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use Mealz\MealBundle\DataFixtures\ORM\LoadDays;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use Mealz\MealBundle\DataFixtures\ORM\LoadMeals;
use Mealz\MealBundle\DataFixtures\ORM\LoadParticipants;
use Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use Mealz\MealBundle\DataFixtures\ORM\LoadWeeks;
use Mealz\AccountingBundle\DataFixtures\ORM\LoadTransactions;
use Symfony\Component\DomCrawler\Link;

/**
 * @package Mealz\MealBundle\Tests\Controller
 * @author Dirk Rauscher <dirk.rauscher@aoe.com>
 */
class DishVariationControllerTest extends AbstractControllerTestCase
{
    public function setUp()
    {
        $this->createAdminClient();
        //$this->mockServices();
        $this->clearAllTables();
        $this->loadFixtures([
            #new LoadWeeks(),
            #new LoadDays(),
            new LoadCategories(),
            new LoadDishes(),
            new LoadDishVariations(),
            #new LoadMeals(),
            new LoadUsers($this->client->getContainer()),
            #new LoadParticipants(),
            #new LoadTransactions,
        ]);
    }

    /**
     * Test calling a form for new dish variation
     */
    public function testGetEmptyFormAction()
    {
            // click add new variation button
        $crawler = $this->client->request('GET', '/dish');
        $link = $crawler->filterXPath("//a[contains(@href,'/variation/new') and contains(@class,'load-edit-form')]")->first()->link();
        $crawler = $this->client->click($link);
        $crawler = $this->getRawResponseCrawler();

            // get dish id from url
        $dishId = $this->grepDishIdFromUri($link);

            // check if there is a form shown for adding a new dish variation
        $node = $crawler->filter('form[action$="/'.$dishId.'/variation/new"]');
        $this->assertTrue($node->count() === 1);
    }

    /**
     * Test creating a new dish variation
     */
    public function testNewVariationAction()
    {
            // Create form data
        #$token = $this->client->getContainer()->get('security.csrf.token_manager')->getToken('dish_type');
        $form['dishvariation'] = array(
            'title_de' => 'dishvariation-TITLE-de'.rand(),
            'title_en' => 'dishvariation-TITLE-en'.rand(),
            #'_token' => $token->getValue()
        );

        $dishId = $this->getHelperObject('dishid');

            // Call controller action
        $m = $this->client->request('POST', '/dish/'.$dishId.'/variation/new', $form);

            // Get persisted entity
        /** @var EntityManager $em */
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $dishVariationRepository = $em->getRepository('MealzMealBundle:DishVariation');
        $dishVariation = $dishVariationRepository->findOneBy(array(
            'title_de' => $form['dishvariation']['title_de'],
            'title_en' => $form['dishvariation']['title_en']
        ));

        // Assertions
        $this->assertNotNull($dishVariation);
        $this->assertInstanceOf('\Mealz\MealBundle\Entity\DishVariation', $dishVariation);
    }


    /**
     * Test adding a new new dishvariation and find it listed in dishes list
     */
    public function testListAction()
    {
        $dishVariation = $this->createDishVariation();
        $this->persistAndFlushAll(array($dishVariation));

        // Request
        $crawler = $this->client->request('GET', '/dish');

        // Get data for assertions from response
        $heading = $crawler->filter('h1')->first()->text();

        $dishVariationTitles = $crawler->filter('.table-row .dish-variation-title');
        $dishVariationTitles->rewind();
        $found = FALSE;

        if ($dishVariationTitles->count() > 0) {
            while ($dishVariationTitles->current() && $found == FALSE) {
                $found = ($dishVariation->getTitle() === trim($dishVariationTitles->current()->nodeValue)) ? TRUE : FALSE;
                $dishVariationTitles->next();
            }
        }

        // Assertions
        $this->assertTrue($found,'Dish variation not found');
        $this->assertEquals('List of dishes', trim($heading));
    }

    /**
     * Test a previously created dishvariation can be edited in a form
     */
    public function testEditAction()
    {
        $dishvariation = $this->createDishVariation();
        $this->persistAndFlushAll(array($dishvariation));

        #$token = $this->client->getContainer()->get('form.csrf_provider')->generateCsrfToken('dish_type');
        $form['dishvariation'] = array(
            'title_de' => 'edited-dishvariation-de-'.rand(),
            'title_en' => 'edited-dishvariation-en-'.rand(),
        #    '_token' => $token
        );

        $this->client->request('POST', '/dish/variation/' . $dishvariation->getId() . '/edit', $form);
        $dishRepository = $this->getDoctrine()->getRepository('MealzMealBundle:DishVariation');
        unset($form['dishvariation']['_token']);
        $editedDishVariation = $dishRepository->findOneBy($form['dishvariation']);

        $this->assertInstanceOf('\Mealz\MealBundle\Entity\DishVariation', $editedDishVariation);
        $this->assertEquals($dishvariation->getId(), $editedDishVariation->getId());
    }

    /**
     * Test calling a non existing dishvariation(ID) to be EDITED leads to a 404 error
     */
    public function testEditActionOfNonExistingDishVariation()
    {
        $this->client->request('POST', '/dish/variation/xxxnon-existing-dishvariation/edit');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test firing a dish DELETION from the admin backend deletes the dishvariation from database
     */
    public function testDeleteAction()
    {
        $dishvariation = $this->createDishVariation();
        $this->persistAndFlushAll(array($dishvariation));

        $dishvariationId = $dishvariation->getId();
        $this->client->request('GET', '/dish/variation/' . $dishvariation->getId() . '/delete');
        $dishvariationRepository = $this->getDoctrine()->getRepository('MealzMealBundle:DishVariation');
        $queryResult = $dishvariationRepository->find($dishvariationId);

        $this->assertNull($queryResult);
    }

    /**
     * Test calling a non existing dish(ID) to be DELETED leads to a 404 error
     */
    public function testDeleteOfNonExistingDishVariation()
    {
        $this->client->request('GET', '/dish/variation/xxxnon-existing-dishvariation/delete');
        $this->assertEquals(404,$this->client->getResponse()->getStatusCode());
    }

#*****************************************************************************************************

    /**
     * Retrieve the first id (integer) that is contained in an URL
     * @param $link Link
     * @return bool | integer $dishId
     */
    protected function grepDishIdFromUri($link) {
        preg_match("|(?<=/)(\d+)(?=/)|",$link->getUri(),$m);
        $dishId = (count($m)) ? $m[0] : FALSE;

        return $dishId;
    }

     /**
     * @param string $type      This function either returns a Crawler('crawler') object or an Id('id'). Default is crawler of a add variation form.
     * @return Crawler
     */
    protected function getHelperObject($type = 'crawler') {
            // click add new variation button
        $crawler = $this->client->request('GET', '/dish');
        $link = $crawler->filterXPath("//a[contains(@href,'/variation/new') and contains(@class,'load-edit-form')]")->first()->link();
        $crawler = $this->client->click($link);
        $crawler = $this->getRawResponseCrawler();

            // get dish id from url
        $dishId = $this->grepDishIdFromUri($link);

        switch (strtolower($type)) {
            case 'dishid';
                return $dishId;
            case 'crawler':
            default:
                return $crawler;
        }
    }

   protected function getRawResponseCrawler()
    {
        $content = $this->client->getResponse()->getContent();
        $uri = 'http://www.mealz.local';
        return new Crawler(json_decode($content), $uri);
    }
}