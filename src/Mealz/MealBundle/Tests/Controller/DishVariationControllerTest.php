<?php

namespace App\Mealz\MealBundle\Tests\Controller;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishRepository;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use Doctrine\ORM\EntityManager;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\Link;

/**
 * @author Dirk Rauscher <dirk.rauscher@aoe.com>
 */
class DishVariationControllerTest extends AbstractControllerTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->clearAllTables();
        $this->loadFixtures([
            new LoadRoles(),
            new LoadUsers(self::$container->get('security.user_password_encoder.generic')),
            new LoadCategories(),
            new LoadDishes(),
            new LoadDishVariations(),
        ]);

        $this->loginAs(self::USER_KITCHEN_STAFF);
    }

    public function testNewForm(): void
    {
        /** @var Dish $dish */
        $dish = $this->getDish();

        $url = '/dish/' . $dish->getId() . '/variation/new';
        $this->client->request('GET', $url);

        // Assert that we get JSON response
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'),
            'DishVariationController::newAction should return HTML response.'
        );

        $crawler = $this->getRawResponseCrawler();
        $formNode = $crawler->filterXPath('//form[@action="' . $url . '"]');
        $this->assertTrue(1 === $formNode->count());

        $inputDeDescNode = $crawler->filterXPath('//input[@name="dishvariation[title_de]"]');
        $this->assertTrue(1 === $inputDeDescNode->count());

        $inputEnDescNode = $crawler->filterXPath('//input[@name="dishvariation[title_en]"]');
        $this->assertTrue(1 === $inputEnDescNode->count());
    }

    public function testCreateDishVariation(): void
    {
        /** @var Dish $dish */
        $dish = $this->getDish(null, true);

        $url = '/dish/' . $dish->getId() . '/variation/new';
        $this->client->request('GET', $url);

        $crawler = $this->getRawResponseCrawler();
        $this->client->submit($crawler->filterXPath('//form[@action="' . $url . '"]')->form([
            'dishvariation[title_de]' => 'new dish variation [de]',
            'dishvariation[title_en]' => 'new dish variation [en]',
        ]), []);

        $this->assertTrue($this->client->getResponse()->isRedirect('/dish'));

        $updatedDishVariation = $this->getDishVariationBy('title_de', 'new dish variation [de]');
        $this->assertEquals('new dish variation [de]', $updatedDishVariation->getTitleDe());
        $this->assertEquals('new dish variation [en]', $updatedDishVariation->getTitleEn());
    }

    public function testEditForm(): void
    {
        /** @var Dish $dish */
        $dish = $this->getDish(null, true);
        $dishVariation = $dish->getVariations()->get(0);

        $url = '/dish/variation/' . $dishVariation->getId() . '/edit';
        $this->client->request('GET', $url);

        // Assert that we get html response
        $this->assertTrue(
            $this->client->getResponse()->headers->contains('Content-Type', 'text/html; charset=UTF-8'),
            'DishVariationController::editAction should return HTML response.'
        );

        $crawler = $this->getRawResponseCrawler();
        $formNode = $crawler->filterXPath('//form[@action="' . $url . '"]');
        $this->assertTrue(1 === $formNode->count());

        $inputDeDescNode = $crawler->filterXPath('//input[@name="dishvariation[title_de]"]');
        $this->assertTrue(1 === $inputDeDescNode->count());

        $inputEnDescNode = $crawler->filterXPath('//input[@name="dishvariation[title_en]"]');
        $this->assertTrue(1 === $inputEnDescNode->count());
    }

    public function testUpdateDishVariation(): void
    {
        /** @var Dish $dish */
        $dish = $this->getDish(null, true);
        $dishVariationId = $dish->getVariations()->get(0)->getId();

        $url = '/dish/variation/' . $dishVariationId . '/edit';
        $this->client->request('GET', $url);

        $crawler = $this->getRawResponseCrawler();
        $this->client->submit($crawler->filterXPath('//form[@action="' . $url . '"]')->form([
            'dishvariation[title_de]' => 'dish variation [de]',
            'dishvariation[title_en]' => 'dish variation [en]',
        ]), []);

        $this->assertTrue($this->client->getResponse()->isRedirect('/dish'));

        $updatedDishVariation = $this->getDishVariationBy('id', $dishVariationId);
        $this->assertEquals('dish variation [de]', $updatedDishVariation->getTitleDe());
        $this->assertEquals('dish variation [en]', $updatedDishVariation->getTitleEn());
    }

    public function testDeleteDishVariation(): void
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

    public function testDeleteNonExistingDishVariation(): void
    {
        $this->client->request('GET', '/dish/variation/1234097354/delete');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test calling a form for new dish variation.
     */
    public function testGetEmptyFormAction(): void
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

        $this->assertTrue(1 === $node->count());
    }

    /**
     * Test creating a new dish variation.
     */
    public function testNewVariationAction(): void
    {
        $dishId = $this->getHelperObject('dishid');
        $formURI = '/dish/' . $dishId . '/variation/new';

        // Create form data
        $form['dishvariation'] = [
            'title_de' => 'dishvariation-TITLE-de' . mt_rand(),
            'title_en' => 'dishvariation-TITLE-en' . mt_rand(),
            '_token' => $this->getFormCSRFToken($formURI, '#dishvariation__token'),
        ];

        // Call controller action
        $this->client->request('POST', $formURI, $form);

        // Get persisted entity
        /** @var EntityManager $entityManager */
        $entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $dishVariationRepo = $entityManager->getRepository(DishVariation::class);
        $dishVariation = $dishVariationRepo->findOneBy([
            'title_de' => $form['dishvariation']['title_de'],
            'title_en' => $form['dishvariation']['title_en'],
        ]);

        $this->assertNotNull($dishVariation);
        $this->assertInstanceOf(DishVariation::class, $dishVariation);
    }

    /**
     * Test adding a new new dishvariation and find it listed in dishes list.
     */
    public function testListAction(): void
    {
        $dish = $this->getDish(null, true);
        $dishVariation = $dish->getVariations()->get(0);

        // Request
        $crawler = $this->client->request('GET', '/dish');

        // Get data for assertions from response
        $heading = $crawler->filter('h1')->first()->text();

        $dishVariationTitles = $crawler->filter('.table-row .dish-variation-title');
        $found = false;

        foreach ($dishVariationTitles as $dishVariationTitle) {
            if (false !== strpos(trim($dishVariationTitle->nodeValue), $dishVariation->getTitle())) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found, 'Dish variation not found');
        $this->assertEquals('List of dishes', trim($heading));
    }

    /**
     * Test a previously created dishvariation can be edited in a form.
     */
    public function testEditAction(): void
    {
        $dish = $this->getDish(null, true);
        $dishVariation = $dish->getVariations()->get(0);
        $formURI = '/dish/variation/' . $dishVariation->getId() . '/edit';

        $form['dishvariation'] = [
            'title_de' => 'edited-dishvariation-de-' . mt_rand(),
            'title_en' => 'edited-dishvariation-en-' . mt_rand(),
            '_token' => $this->getFormCSRFToken($formURI, '#dishvariation__token'),
        ];

        $this->client->request('POST', $formURI, $form);
        $dishRepository = $this->getDoctrine()->getRepository(DishVariation::class);
        unset($form['dishvariation']['_token']);
        $editedDishVariation = $dishRepository->findOneBy($form['dishvariation']);

        $this->assertInstanceOf(DishVariation::class, $editedDishVariation);
        $this->assertEquals($dishVariation->getId(), $editedDishVariation->getId());
    }

    /**
     * Test calling a non existing dishvariation(ID) to be EDITED leads to a 404 error.
     */
    public function testEditActionOfNonExistingDishVariation(): void
    {
        $this->client->request('POST', '/dish/variation/xxxnon-existing-dishvariation/edit');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test firing a dish DELETION from the admin backend deletes the dishvariation from database.
     */
    public function testDeleteAction(): void
    {
        $dish = $this->getDish(null, true);
        /* @var $dishVariation DishVariation */
        $dishVariation = $dish->getVariations()->get(0);

        $dishVariationId = $dishVariation->getId();
        $this->client->request('GET', "/dish/variation/$dishVariationId/delete");
        $dishVariationRepo = $this->getDoctrine()->getRepository(DishVariation::class);
        $queryResult = $dishVariationRepo->find($dishVariationId);

        $this->assertEquals(null, $queryResult);
    }

    /**
     * Test calling a non existing dish(ID) to be DELETED leads to a 404 error.
     */
    public function testDeleteOfNonExistingDishVariation(): void
    {
        $this->client->request('GET', '/dish/variation/xxxnon-existing-dishvariation/delete');
        $this->assertEquals(404, $this->client->getResponse()->getStatusCode());
    }

    protected function getRawResponseCrawler(): Crawler
    {
        $content = $this->client->getResponse()->getContent();
        $hostUrl = $this->getHostUrl();

        return new Crawler($content, $hostUrl);
    }

    /**
     * Retrieve the first id (integer) that is contained in an URL.
     *
     * @param $link Link
     *
     * @return bool|int
     */
    private function grepDishIdFromUri(Link $link)
    {
        preg_match("|(?<=/)(\d+)(?=/)|", $link->getUri(), $match);

        return (count($match)) ? (int) $match[0] : false;
    }

    /**
     * @param string $type This function either returns a Crawler('crawler') object or an Id('id'). Default is crawler of a add variation form.
     *
     * @return Crawler
     */
    private function getHelperObject($type = 'crawler')
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
     * @param int|null $identifier      Dish ID
     * @param bool     $dishVarRequired if TRUE and no identifier is given the method returns the first dish
     *                                  having at least ONE variation
     *
     * @return Dish
     */
    private function getDish($identifier = null, $dishVarRequired = false)
    {
        /** @var DishRepository $dishRepository */
        $dishRepository = $this->getDoctrine()->getRepository(Dish::class);
        $dish = null;

        if ($identifier > 0) {
            $dish = $dishRepository->find($identifier);
            if (true === $dish instanceof Dish) {
                return $dish;
            }
        }

        $result = $dishRepository->findBy([], ['id' => 'ASC']);

        if (false === is_array($result) || 0 === count($result)) {
            $this->fail('Failed to fetch test dish.');
        }

        if (false == $dishVarRequired) {
            $dish = (is_array($result) && count($result)) ? $result[0] : null;
        } else {
            foreach ($result as $item) {
                if (true == $item->hasVariations()) {
                    $dish = $item;
                    break;
                }
            }
        }

        if (false === $dish instanceof Dish) {
            $this->fail('Failed to fetch test dish.');
        }

        return $dish;
    }

    /**
     * @param string $attribute
     * @param mixed  $value
     * @param bool   $throwError
     *
     * @return DishVariation
     */
    private function getDishVariationBy($attribute, $value, $throwError = true)
    {
        $dishVariationRepo = $this->getDoctrine()->getRepository(DishVariation::class);

        if ('id' === $attribute) {
            $dishVariation = $dishVariationRepo->find($value);
        } else {
            $dishVariation = $dishVariationRepo->findOneBy([$attribute => $value]);
        }

        if (false === $dishVariation instanceof DishVariation && true === $throwError) {
            $this->fail('Failed to fetch test dish variation.');
        }

        return $dishVariation;
    }

    private function getHostUrl(): string
    {
        $host = 'http://localhost/app.php/';
        if (self::$kernel->getContainer()->hasParameter('mealz.host')) {
            $host = self::$kernel->getContainer()->getParameter('mealz.host');
            $host = rtrim($host, '/') . '/';
        }

        return $host;
    }
}
