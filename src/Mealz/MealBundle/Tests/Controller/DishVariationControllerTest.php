<?php

namespace App\Mealz\MealBundle\Tests\Controller;

use Doctrine\ORM\EntityManager;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishRepository;
use App\Mealz\MealBundle\Entity\DishVariation;
use Symfony\Component\DomCrawler\Crawler;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use Symfony\Component\DomCrawler\Link;

/**
 * @package Mealz\MealBundle\Tests\Controller
 * @author Dirk Rauscher <dirk.rauscher@aoe.com>
 */
class DishVariationControllerTest extends AbstractControllerTestCase
{
    protected function setUp(): void
    {
        $this->createAdminClient();
        //$this->mockServices();
        $this->clearAllTables();
        $this->loadFixtures(
            [
                new LoadCategories(),
                new LoadDishes(),
                new LoadDishVariations(),
                new LoadUsers(self::$container->get('security.user_password_encoder.generic')),
            ]
        );
    }

    /**
     * @test
     */
    public function newForm()
    {
        /** @var Dish $dish */
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
        /** @var Dish $dish */
        $dish = $this->getDish(null, true);

        $url = '/dish/' . $dish->getId() . '/variation/new';
        $this->client->request('GET', $url);

        $crawler = $this->getRawResponseCrawler();
        $this->client->submit($crawler->filterXPath('//form[@action="' . $url . '"]')->form([
            'dishvariation[title_de]' => 'new dish variation [de]',
            'dishvariation[title_en]' => 'new dish variation [en]'
        ]), []);

        $this->assertTrue($this->client->getResponse()->isRedirect('/dish'));

        /** @var DishVariation $updatedDishVariation */
        $updatedDishVariation = $this->getDishVariationBy('title_de', 'new dish variation [de]');
        $this->assertEquals('new dish variation [de]', $updatedDishVariation->getTitleDe());
        $this->assertEquals('new dish variation [en]', $updatedDishVariation->getTitleEn());
    }

    /**
     * @test
     */
    public function editForm()
    {
        /** @var Dish $dish */
        $dish = $this->getDish(null, true);
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
        /** @var Dish $dish */
        $dish = $this->getDish(null, true);
        $dishVariationId = $dish->getVariations()->get(0)->getId();

        $url = '/dish/variation/' . $dishVariationId . '/edit';
        $this->client->request('GET', $url);

        $crawler = $this->getRawResponseCrawler();
        $this->client->submit($crawler->filterXPath('//form[@action="' . $url . '"]')->form([
            'dishvariation[title_de]' => 'dish variation [de]',
            'dishvariation[title_en]' => 'dish variation [en]'
        ]), []);

        $this->assertTrue($this->client->getResponse()->isRedirect('/dish'));

        /** @var DishVariation $updatedDishVariation */
        $updatedDishVariation = $this->getDishVariationBy('id', $dishVariationId);
        $this->assertEquals('dish variation [de]', $updatedDishVariation->getTitleDe());
        $this->assertEquals('dish variation [en]', $updatedDishVariation->getTitleEn());
    }

    /**
     * @test
     */
    public function deleteDishVariation()
    {
        /** @var Dish $dish */
        $dish = $this->getDish(null, true);
        $dishVariation = $dish->getVariations()->get(0);
        $dishVariationId = $dishVariation->getId();
        $this->assertTrue($dishVariation->isEnabled());

        $this->client->request('GET', '/dish/variation/' . $dishVariation->getId() . '/delete');
        $this->assertTrue($this->client->getResponse()->isRedirect('/dish'));

        $updatedDishVariation = $this->getDishVariationBy('id', $dishVariationId, false);
        if ($updatedDishVariation instanceof DishVariation) {
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
        $this->client->click($link);
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
        $this->client->request('POST', '/dish/' . $dishId . '/variation/new', $form);

        // Get persisted entity
        /** @var EntityManager $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $dishVariationRepo = $entityManager->getRepository('MealzMealBundle:DishVariation');
        $dishVariation = $dishVariationRepo->findOneBy(
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
        $dish = $this->getDish(null, true);
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
                $found = (
                    strpos(
                        trim($dishVariationTitles->current()->nodeValue),
                        $dishVariation->getTitle()
                    ) !== false
                ) ? true : false;
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
        $dish = $this->getDish(null, true);
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
        $dish = $this->getDish(null, true);
        /* @var $dishVariation DishVariation */
        $dishVariation = $dish->getVariations()->get(0);

        $dishVariationId = $dishVariation->getId();
        $this->client->request('GET', "/dish/variation/$dishVariationId/delete");
        $dishVariationRepo = $this->getDoctrine()->getRepository('MealzMealBundle:DishVariation');
        $queryResult = $dishVariationRepo->find($dishVariationId);

        $this->assertEquals(null, $queryResult);
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
        preg_match("|(?<=/)(\d+)(?=/)|", $link->getUri(), $match);
        $dishId = (count($match)) ? $match[0] : false;

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
        $this->client->click($link);
        $crawler = $this->getRawResponseCrawler();

        // get dish id from url
        $dishId = $this->grepDishIdFromUri($link);

        switch (strtolower($type)) {
            case 'dishid':
                return $dishId;
            case 'crawler':
            default:
                return $crawler;
        }
    }

    /**
     * Gets the dish by dish-id.
     *
     * If no dish-identifier is specified then it returns the test dish with lowest identifier.
     *
     * @param  integer|NULL $identifier Dish ID
     * @param  bool $dishVarRequired If TRUE and no identifier is given the method returns the first dish
     *                                              having at least ONE variation.
     * @return Dish
     */
    private function getDish($identifier = null, $dishVarRequired = false)
    {
        /** @var DishRepository $dishRepository */
        $dishRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Dish');
        $dish = null;

        if ($identifier > 0) {
            $dish = $dishRepository->find($identifier);
            if ($dish instanceof Dish === true) {
                return $dish;
            }
        }

        $result = $dishRepository->findBy([], ['id' => 'ASC']);

        if (is_array($result) === false || count($result) === false) {
            $this->fail('Failed to fetch test dish.');
        }

        if ($dishVarRequired == false) {
            $dish = (is_array($result) && count($result)) ? $result[0] : null;
        } else {
            foreach ($result as $item) {
                if ($item->hasVariations() == true) {
                    $dish = $item;
                    break;
                }
            }
        }

        if ($dish instanceof Dish === false) {
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
        $dishVariationRepo = $this->getDoctrine()->getRepository('MealzMealBundle:DishVariation');

        if ($attribute === 'id') {
            $dishVariation = $dishVariationRepo->find($value);
        } else {
            $dishVariation = $dishVariationRepo->findOneBy([$attribute => $value]);
        }

        if ($dishVariation instanceof DishVariation === false && $throwError === true) {
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
