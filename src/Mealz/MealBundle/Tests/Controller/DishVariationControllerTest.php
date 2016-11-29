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
        $this->loadFixtures(
            [
                new LoadCategories(),
                new LoadDishes(),
                new LoadDishVariations(),
                new LoadUsers($this->client->getContainer()),
            ]
        );
    }

    /**
     * @test
     */
    public function newForm()
    {
        /** @var \Mealz\MealBundle\Entity\Dish $dish */
        $dish = $this->getDish();

        $url = '/dish/' . $dish->getId() . '/variation/new';
        $this->client->request('GET', $url);

        // Assert that we get JSON response
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            'DishVariationController::newAction should return JSON response.'
        );

        $crawler = $this->getRawResponseCrawler();
        $formNode = $crawler->filterXPath('//form[@action="' . $url . '"]');
        $this->assertTrue($formNode->count() === 1);

        $inputDeDescNode = $crawler->filterXPath('//input[@name="dishvariation[title_de]"]');
        $this->assertTrue($inputDeDescNode->count() === 1);

        $inputEnDescNode = $crawler->filterXPath('//input[@name="dishvariation[title_en]"]');
        $this->assertTrue($inputEnDescNode->count() === 1);
    }

    /**
     * @test
     */
    public function createDishVariation()
    {
        /** @var \Mealz\MealBundle\Entity\Dish $dish */
        $dish = $this->getDish(NULL, true);

        $url = '/dish/' . $dish->getId() . '/variation/new';
        $this->client->request('GET', $url);

        $crawler = $this->getRawResponseCrawler();
        $this->client->submit($crawler->filterXPath('//form[@action="' . $url . '"]')->form([
            'dishvariation[title_de]' => 'new dish variation [de]',
            'dishvariation[title_en]' => 'new dish variation [en]'
        ]));

        $this->assertTrue($this->client->getResponse()->isRedirect('/dish'));

        /** @var \Mealz\MealBundle\Entity\DishVariation $updatedDishVariation */
        $updatedDishVariation = $this->getDishVariationBy('title_de', 'new dish variation [de]');
        $this->assertEquals('new dish variation [de]', $updatedDishVariation->getTitleDe());
        $this->assertEquals('new dish variation [en]', $updatedDishVariation->getTitleEn());
    }

    /**
     * @test
     */
    public function editForm()
    {
        /** @var \Mealz\MealBundle\Entity\Dish $dish */
        $dish = $this->getDish(NULL, true);
        $dishVariation = $dish->getVariations()->get(0);

        $url = '/dish/variation/' . $dishVariation->getId() . '/edit';
        $this->client->request('GET', $url);

        // Assert that we get JSON response
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'application/json'),
            'DishVariationController::editAction should return JSON response.'
        );

        $crawler = $this->getRawResponseCrawler();
        $formNode = $crawler->filterXPath('//form[@action="' . $url . '"]');
        $this->assertTrue($formNode->count() === 1);

        $inputDeDescNode = $crawler->filterXPath('//input[@name="dishvariation[title_de]"]');
        $this->assertTrue($inputDeDescNode->count() === 1);

        $inputEnDescNode = $crawler->filterXPath('//input[@name="dishvariation[title_en]"]');
        $this->assertTrue($inputEnDescNode->count() === 1);
    }

    /**
     * @test
     */
    public function updateDishVariation()
    {
        /** @var \Mealz\MealBundle\Entity\Dish $dish */
        $dish = $this->getDish(NULL, true);
        $dishVariationId = $dish->getVariations()->get(0)->getId();

        $url = '/dish/variation/' . $dishVariationId . '/edit';
        $this->client->request('GET', $url);

        $crawler = $this->getRawResponseCrawler();
        $this->client->submit($crawler->filterXPath('//form[@action="' . $url . '"]')->form([
            'dishvariation[title_de]' => 'dish variation [de]',
            'dishvariation[title_en]' => 'dish variation [en]'
        ]));

        $this->assertTrue($this->client->getResponse()->isRedirect('/dish'));

        /** @var \Mealz\MealBundle\Entity\DishVariation $updatedDishVariation */
        $updatedDishVariation = $this->getDishVariationBy('id', $dishVariationId);
        $this->assertEquals('dish variation [de]', $updatedDishVariation->getTitleDe());
        $this->assertEquals('dish variation [en]', $updatedDishVariation->getTitleEn());
    }

    /**
     * @test
     */
    public function deleteDishVariation()
    {
        /** @var \Mealz\MealBundle\Entity\Dish $dish */
        $dish = $this->getDish(NULL, true);
        $dishVariation = $dish->getVariations()->get(0);
        $dishVariationId = $dishVariation->getId();
        $this->assertTrue($dishVariation->isEnabled());

        $this->client->request('GET', '/dish/variation/' . $dishVariation->getId() . '/delete');
        $this->assertTrue($this->client->getResponse()->isRedirect('/dish'));

        $updatedDishVariation = $this->getDishVariationBy('id', $dishVariationId, false);
        if($updatedDishVariation instanceof \Mealz\MealBundle\Entity\DishVariation){
            $this->assertFalse($updatedDishVariation->isEnabled());
        } else {
            $this->assertNull($updatedDishVariation);
        }
    }

    /**
     * @test
     */
    public function deleteNonExistingDishVariation()
    {
        $this->client->request('GET', '/dish/variation/1234097354/delete');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }


    /**
     * Test calling a form for new dish variation
     */
    public function testGetEmptyFormAction()
    {
        // click add new variation button
        $crawler = $this->client->request('GET', '/dish');
        $link = $crawler->filterXPath(
            "//a[contains(@href,'/variation/new') and contains(@class,'load-edit-form')]"
        )->first()->link();
        $crawler = $this->client->click($link);
        $crawler = $this->getRawResponseCrawler();

        // get dish id from url
        $dishId = $this->grepDishIdFromUri($link);

        // check if there is a form shown for adding a new dish variation
        $node = $crawler->filter('form[action$="/' . $dishId . '/variation/new"]');
        $this->assertTrue($node->count() === 1);
    }

    /**
     * Test creating a new dish variation
     */
    public function testNewVariationAction()
    {
        // Create form data
        $form['dishvariation'] = array(
            'title_de' => 'dishvariation-TITLE-de' . rand(),
            'title_en' => 'dishvariation-TITLE-en' . rand(),
        );

        $dishId = $this->getHelperObject('dishid');

        // Call controller action
        $m = $this->client->request('POST', '/dish/' . $dishId . '/variation/new', $form);

        // Get persisted entity
        /** @var EntityManager $em */
        $em = $this->client->getContainer()->get('doctrine')->getManager();
        $dishVariationRepository = $em->getRepository('MealzMealBundle:DishVariation');
        $dishVariation = $dishVariationRepository->findOneBy(
            array(
                'title_de' => $form['dishvariation']['title_de'],
                'title_en' => $form['dishvariation']['title_en'],
            )
        );

        // Assertions
        $this->assertNotNull($dishVariation);
        $this->assertInstanceOf('\Mealz\MealBundle\Entity\DishVariation', $dishVariation);
    }


    /**
     * Test adding a new new dishvariation and find it listed in dishes list
     */
    public function testListAction()
    {
        #$dishVariation = $this->createDishVariation();
        #$this->persistAndFlushAll(array($dishVariation));
        $dish = $this->getDish(NULL, true);
        $dishVariation = $dish->getVariations()->get(0);

        // Request
        $crawler = $this->client->request('GET', '/dish');

        // Get data for assertions from response
        $heading = $crawler->filter('h1')->first()->text();

        $dishVariationTitles = $crawler->filter('.table-row .dish-variation-title');
        $dishVariationTitles->rewind();
        $found = false;

        if ($dishVariationTitles->count() > 0) {
            while ($dishVariationTitles->current() && $found == false) {
                $found = ($dishVariation->getTitle() === trim(
                        $dishVariationTitles->current()->nodeValue
                    )) ? true : false;
                $dishVariationTitles->next();
            }
        }

        // Assertions
        $this->assertTrue($found, 'Dish variation not found');
        $this->assertEquals('List of dishes', trim($heading));
    }

    /**
     * Test a previously created dishvariation can be edited in a form
     */
    public function testEditAction()
    {
        $dish = $this->getDish(NULL, true);
        $dishVariation = $dish->getVariations()->get(0);

        $form['dishvariation'] = array(
            'title_de' => 'edited-dishvariation-de-' . rand(),
            'title_en' => 'edited-dishvariation-en-' . rand(),
        );

        $this->client->request('POST', '/dish/variation/' . $dishVariation->getId() . '/edit', $form);
        $dishRepository = $this->getDoctrine()->getRepository('MealzMealBundle:DishVariation');
        unset($form['dishvariation']['_token']);
        $editedDishVariation = $dishRepository->findOneBy($form['dishvariation']);

        $this->assertInstanceOf('\Mealz\MealBundle\Entity\DishVariation', $editedDishVariation);
        $this->assertEquals($dishVariation->getId(), $editedDishVariation->getId());
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
        #$dishvariation = $this->createDishVariation();
        #$this->persistAndFlushAll(array($dishvariation));
        $dish = $this->getDish(NULL, true);
        $dishvariation = $dish->getVariations()->get(0);

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
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * @return Crawler
     */
    protected function getRawResponseCrawler()
    {
        $content = $this->client->getResponse()->getContent();
        $hostUrl = $this->getHostUrl();
        return new Crawler(json_decode($content), $hostUrl);
    }

    /**
     * Retrieve the first id (integer) that is contained in an URL
     * @param $link Link
     * @return bool | integer $dishId
     */
    protected function grepDishIdFromUri($link)
    {
        preg_match("|(?<=/)(\d+)(?=/)|", $link->getUri(), $m);
        $dishId = (count($m)) ? $m[0] : false;

        return $dishId;
    }

    /**
     * @param string $type This function either returns a Crawler('crawler') object or an Id('id'). Default is crawler of a add variation form.
     * @return Crawler
     */
    protected function getHelperObject($type = 'crawler')
    {
        // click add new variation button
        $crawler = $this->client->request('GET', '/dish');
        $link = $crawler->filterXPath(
            "//a[contains(@href,'/variation/new') and contains(@class,'load-edit-form')]"
        )->first()->link();
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

    /**
     * Gets the dish by dish-id.
     *
     * If no dish-id is specified then it returns the test dish with lowest id.
     *
     * @param  integer|NULL $id Dish ID
     * @param  bool $dishvariationRequired If TRUE and no id is given the method returns the first dish
     *                                              having at least ONE variation.
     * @return Dish
     */
    private function getDish($id = NULL, $dishvariationRequired = false)
    {
        /** @var \Mealz\MealBundle\Entity\DishRepository $dishRepository */
        $dishRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Dish');
        $dish = NULL;

        if ($id > 0) {
            $dish = $dishRepository->find($id);
        } else {
            $result = $dishRepository->findBy([], ['id' => 'ASC']);
            if (is_array($result) && count($result)) {
                if ($dishvariationRequired == false) {
                    $dish = (is_array($result) && count($result)) ? $result[0] : NULL;
                } else {
                    foreach ($result as $item) {
                        if ($item->hasVariations() == true) {
                            $dish = $item;
                            break;
                        }
                    }
                }
            }
        }

        if (!$dish instanceof \Mealz\MealBundle\Entity\Dish) {
            $this->fail('Failed to fetch test dish.');
        }

        return $dish;
    }

    /**
     * @param  string $attribute
     * @param  mixed $value
     * @param  bool $throwError
     * @return DishVariation
     */
    private function getDishVariationBy($attribute, $value, $throwError = true)
    {
        $dishVariationRepository = $this->getDoctrine()->getRepository('MealzMealBundle:DishVariation');

        if ($attribute === 'id') {
            $dishVariation = $dishVariationRepository->find($value);
        } else {
            $dishVariation = $dishVariationRepository->findOneBy([$attribute => $value]);
        }

        if (!$dishVariation instanceof \Mealz\MealBundle\Entity\DishVariation && $throwError) {
            $this->fail('Failed to fetch test dish variation.');
        }

        return $dishVariation;
    }

    /**
     * @return string
     */
    private function getHostUrl()
    {
        $host = 'http://localhost/app.php/';
        if (self::$kernel->getContainer()->hasParameter('mealz.host')) {
            $host = self::$kernel->getContainer()->getParameter('mealz.host');
            $host = rtrim($host, '/') . '/';
        }
        return $host;

    }
}