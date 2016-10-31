<?php

namespace Mealz\MealBundle\Tests\Controller;


use Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use Mealz\UserBundle\DataFixtures\ORM\LoadUsers;

use Mealz\MealBundle\Entity\Dish;
use Mealz\MealBundle\Entity\DishVariation;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @package Mealz\MealBundle\Tests\Controller
 * @author Chetan Thapliyal <chetan.thapliyal@aoe.com>
 */
class DishVariationControllerTest extends AbstractControllerTestCase
{
	public function setUp()
	{
		parent::setUp();

		$this->createAdminClient();
		$this->clearAllTables();
		$this->loadFixtures([
			new LoadCategories(),
			new LoadDishes(),
			new LoadDishVariations(),
			new LoadUsers($this->client->getContainer())
		]);

		// Send all requests as AJAX
		$this->client->setServerParameter('HTTP_X-Requested-With', 'XMLHttpRequest');

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
		$formNode = $crawler->filterXPath('//form[@action="'.$url.'"]');
		$this->assertTrue($formNode->count() === 1);

		$inputDeDescNode = $crawler->filterXPath('//input[@name="dish_variation_form[description_de]"]');
		$this->assertTrue($inputDeDescNode->count() === 1);

		$inputEnDescNode = $crawler->filterXPath('//input[@name="dish_variation_form[description_en]"]');
		$this->assertTrue($inputEnDescNode->count() === 1);
	}

	/**
	 * @test
	 */
	public function createDishVariation()
	{
		/** @var \Mealz\MealBundle\Entity\Dish $dish */
		$dish = $this->getDish();

		$url = '/dish/' . $dish->getId() . '/variation/new';
		$this->client->request('GET', $url);

		$crawler = $this->getRawResponseCrawler();
		$this->client->submit($crawler->filterXPath('//form[@action="'.$url.'"]')->form([
			'dish_variation_form[description_de]' => 'new dish variation [de]',
			'dish_variation_form[description_en]' => 'new dish variation [en]'
		]));

		$this->assertTrue($this->client->getResponse()->isRedirect('/dish'));

		/** @var \Mealz\MealBundle\Entity\DishVariation $updatedDishVariation */
		$updatedDishVariation = $this->getDishVariationBy('description_de', 'new dish variation [de]');
		$this->assertEquals('new dish variation [de]', $updatedDishVariation->getDescriptionDe());
		$this->assertEquals('new dish variation [en]', $updatedDishVariation->getDescriptionEn());
	}

	/**
	 * @test
	 */
	public function editForm()
	{
		/** @var \Mealz\MealBundle\Entity\Dish $dish */
		$dish = $this->getDish();
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

		$inputDeDescNode = $crawler->filterXPath('//input[@name="dish_variation_form[description_de]"]');
		$this->assertTrue($inputDeDescNode->count() === 1);

		$inputEnDescNode = $crawler->filterXPath('//input[@name="dish_variation_form[description_en]"]');
		$this->assertTrue($inputEnDescNode->count() === 1);
	}

	/**
	 * @test
	 */
	public function updateDishVariation()
	{
		/** @var \Mealz\MealBundle\Entity\Dish $dish */
		$dish = $this->getDish();
		$dishVariationId = $dish->getVariations()->get(0)->getId();

		$url = '/dish/variation/' . $dishVariationId . '/edit';
		$this->client->request('GET', $url);

		$crawler = $this->getRawResponseCrawler();
		$this->client->submit($crawler->filterXPath('//form[@action="'.$url.'"]')->form([
			'dish_variation_form[description_de]' => 'dish variation [de]',
			'dish_variation_form[description_en]' => 'dish variation [en]'
		]));

		$this->assertTrue($this->client->getResponse()->isRedirect('/dish'));

		/** @var \Mealz\MealBundle\Entity\DishVariation $updatedDishVariation */
		$updatedDishVariation = $this->getDishVariationBy('id', $dishVariationId);
		$this->assertEquals('dish variation [de]', $updatedDishVariation->getDescriptionDe());
		$this->assertEquals('dish variation [en]', $updatedDishVariation->getDescriptionEn());
	}

	/**
	 * @test
	 */
	public function deleteDishVariation()
	{
		/** @var \Mealz\MealBundle\Entity\Dish $dish */
		$dish = $this->getDish();
		$dishVariation = $dish->getVariations()->get(0);
		$this->assertTrue($dishVariation->isEnabled());

		$this->client->request('GET', '/dish/variation/' . $dishVariation->getId() . '/delete');
		$this->assertTrue($this->client->getResponse()->isRedirect('/dish'));

		$updatedDishVariation = $this->getDishVariationBy('id', $dishVariation->getId());
		$this->assertFalse($updatedDishVariation->isEnabled());
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
	 * Gets the dish by dish-id.
	 *
	 * If no dish-id is specified then it returns the test dish with lowest id.
	 *
	 * @param  integer $id      Dish ID
	 * @return Dish
	 */
	private function getDish($id = 0)
	{
		/** @var \Mealz\MealBundle\Entity\DishRepository $dishRepository */
		$dishRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Dish');

		if ($id > 0) {
			$dish = $dishRepository->find($id);
		} else {
			$result = $dishRepository->findBy([], ['id' => 'ASC'], 1, 0);
			$dish = (is_array($result) && count($result)) ? $result[0] : NULL;
		}

		if (!($dish instanceof Dish)) {
			$this->fail('Failed to fetch test dish.');
		}

		return $dish;
	}

	/**
	 * @param  string $attribute
	 * @param  mixed  $value
	 * @return DishVariation
	 */
	private function getDishVariationBy($attribute, $value)
	{
		$dishVariationRepository = $this->getDoctrine()->getRepository('MealzMealBundle:DishVariation');

		if ($attribute === 'id') {
			$dishVariation = $dishVariationRepository->find($value);
		} else {
			$dishVariation = $dishVariationRepository->findOneBy([$attribute => $value]);
		}

		if (!($dishVariation instanceof DishVariation)) {
			$this->fail('Failed to fetch test dish variation.');
		}

		return $dishVariation;
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
	 * @return string
	 */
	private function getHostUrl()
	{
		$host = self::$kernel->getContainer()->getParameter('mealz.host');
		$host = rtrim($host, '/') . '/';
		return $host;
	}
}
