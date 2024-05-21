<?php

namespace App\Mealz\MealBundle\Tests\Controller;

use App\Mealz\MealBundle\DataFixtures\ORM\LoadCategories;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishes;
use App\Mealz\MealBundle\DataFixtures\ORM\LoadDishVariations;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Repository\DishRepository;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadRoles;
use App\Mealz\UserBundle\DataFixtures\ORM\LoadUsers;
use Symfony\Component\HttpFoundation\Response;

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
            new LoadUsers(self::getContainer()->get('security.user_password_hasher')),
            new LoadCategories(),
            new LoadDishes(),
            new LoadDishVariations(),
        ]);

        $this->loginAs(self::USER_KITCHEN_STAFF);
    }

    /**
     * Test creating a new dish variation.
     */
    public function testNew(): void
    {
        /** @var Dish $dish */
        $dish = $this->getDish();

        $data = json_encode([
            'titleDe' => 'Test De Var123',
            'titleEn' => 'Test En Var123',
        ]);

        $this->client->request('POST', '/api/dishes/'.$dish->getSlug().'/variation', [], [], [], $data);
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $found = false;

        foreach ($dish->getVariations() as $variation) {
            if ('Test De Var123' === $variation->getTitleDe() && 'Test En Var123' === $variation->getTitleEn()) {
                $found = true;
                break;
            }
        }

        $this->assertTrue($found);
    }

    public function testUpdate(): void
    {
        /** @var Dish $dish */
        $dish = $this->getDish(null, true);
        $dishVariation = $dish->getVariations()->get(0);

        $data = json_encode([
            'titleDe' => 'Test De Var123',
            'titleEn' => 'Test En Var123',
        ]);

        $this->client->request('PUT', '/api/dishes/variation/'.$dishVariation->getSlug(), [], [], [], $data);
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $updatedDishVariation = $this->getDishVariationBy('id', $dishVariation->getId(), false);
        $this->assertNotNull($updatedDishVariation);
        $this->assertInstanceOf(DishVariation::class, $updatedDishVariation);
        $this->assertEquals('Test De Var123', $updatedDishVariation->getTitleDe());
        $this->assertEquals('Test En Var123', $updatedDishVariation->getTitleEn());
    }

    public function testDeleteDishVariation(): void
    {
        /** @var DishVariation $dishVariation */
        $dishVariation = $this->getDish(null, true)->getVariations()->get(0);
        $dishVariationId = $dishVariation->getId();
        $this->assertTrue($dishVariation->isEnabled());

        $this->client->request('DELETE', '/api/dishes/variation/'.$dishVariation->getSlug());
        $this->assertEquals(Response::HTTP_OK, $this->client->getResponse()->getStatusCode());

        $updatedDishVariation = $this->getDishVariationBy('id', $dishVariationId, false);
        if ($updatedDishVariation instanceof DishVariation) {
            $this->assertFalse($updatedDishVariation->isEnabled());
        } else {
            $this->assertNull($updatedDishVariation);
        }
    }

    public function testDeleteNonExistingDishVariation(): void
    {
        $this->client->request('DELETE', '/api/dishes/variation/non-existent-dish-variation');
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Test calling a non existing dishvariation to be EDITED leads to a 404 error.
     */
    public function testUpdateActionOfNonExistingDishVariation(): void
    {
        $this->client->request('PUT', '/api/dishes/variation/non-existing-dishvariation');
        $this->assertEquals(Response::HTTP_NOT_FOUND, $this->client->getResponse()->getStatusCode());
    }

    /**
     * Gets the dish by dish-id.
     *
     * If no dish-identifier is specified then it returns the test dish with lowest identifier.
     *
     * @param int|null $identifier Dish ID
     * @param bool $dishVarRequired if TRUE and no identifier is given the method returns the first dish
     *                                  having at least ONE variation
     *
     * @return Dish|null
     */
    private function getDish(int $identifier = null, bool $dishVarRequired = false): ?Dish
    {
        /** @var DishRepository $dishRepository */
        $dishRepository = self::getContainer()->get(DishRepository::class);
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

        if (!$dishVarRequired) {
            $dish = (is_array($result) && count($result)) ? $result[0] : null;
        } else {
            foreach ($result as $item) {
                if ($item->hasVariations()) {
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
     * @param mixed $value
     * @param bool $throwError
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
}
