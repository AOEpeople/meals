<?php

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Repository\DishRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Security("is_granted('ROLE_KITCHEN_STAFF')")
 */
class DishVariationController extends BaseController
{
    private float $defaultPrice;
    private DishRepository $dishRepository;
    private EntityManagerInterface $em;

    public function __construct(
        float $price,
        DishRepository $dishRepository,
        EntityManagerInterface $em
    ) {
        $this->defaultPrice = $price;
        $this->dishRepository = $dishRepository;
        $this->em = $em;
    }

   public function new(Request $request, Dish $dish): JsonResponse
   {
        try {
            $parameters = json_decode($request->getContent(), true);

            $dishVariation = new DishVariation();
            $dishVariation->setParent($dish);
            $dishVariation->setOneServingSize($dish->hasOneServingSize());
            $dishVariation->setPrice($this->defaultPrice);

            if (isset($parameters['titleDe']) && isset($parameters['titleEn'])) {
                $dishVariation->setTitleDe($parameters['titleDe']);
                $dishVariation->setTitleEn($parameters['titleEn']);
            } else {
                throw new Exception('Title not set');
            }

            $this->em->persist($dishVariation);
            $this->em->flush();

            return new JsonResponse(['status' => 'success'], 200);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['status' => $e->getMessage()], 500);
        }
   }

   public function update(Request $request, DishVariation $dishVariation): JsonResponse
   {
        try {
            $parameters = json_decode($request->getContent(), true);
            if (isset($parameters['titleDe'])) {
                $dishVariation->setTitleDe($parameters['titleDe']);
            }
            if (isset($parameters['titleEn'])) {
                $dishVariation->setTitleEn($parameters['titleEn']);
            }

            $this->em->persist($dishVariation);
            $this->em->flush();

            return new JsonResponse($dishVariation, 200);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['status' => $e->getMessage()], 500);
        }
   }

    public function delete(DishVariation $dishVariation): JsonResponse
    {
        try {
            // hide the dish variation if it has been assigned to a meal, else delete it
            if ($this->dishRepository->hasDishAssociatedMeals($dishVariation)) {
                $dishVariation->setEnabled(false);
                $this->em->persist($dishVariation);
            } else {
                $this->em->remove($dishVariation);
            }
            $this->em->flush();

            return new JsonResponse(['status' => 'success'], 200);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['status' => $e->getMessage()], 500);
        }
    }
}
