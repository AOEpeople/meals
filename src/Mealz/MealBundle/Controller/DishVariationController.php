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

    /**
     * Creates a new dish variation.
     */
    public function new(Request $request, Dish $dish): JsonResponse
    {
        try {
            $parameters = json_decode($request->getContent(), true);

            $dishVariation = new DishVariation();
            $dishVariation->setParent($dish);
            $dishVariation->setOneServingSize($dish->hasOneServingSize());
            $dishVariation->setPrice($this->defaultPrice);
            $dishVariation->setCategory($dish->getCategory());

            if (true === isset($parameters['titleDe']) && true === isset($parameters['titleEn'])) {
                $dishVariation->setTitleDe($parameters['titleDe']);
                $dishVariation->setTitleEn($parameters['titleEn']);
            } else {
                throw new Exception('202: Title not set');
            }

            $this->em->persist($dishVariation);
            $this->em->flush();

            return new JsonResponse(null, \Symfony\Component\HttpFoundation\Response::HTTP_OK);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['message' => $e->getMessage()], \Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Updates a dish variation.
     */
    public function update(Request $request, DishVariation $dishVariation): JsonResponse
    {
        try {
            $parameters = json_decode($request->getContent(), true);
            if (true === isset($parameters['titleDe'])) {
                $dishVariation->setTitleDe($parameters['titleDe']);
            }
            if (true === isset($parameters['titleEn'])) {
                $dishVariation->setTitleEn($parameters['titleEn']);
            }

            $this->em->persist($dishVariation);
            $this->em->flush();

            return new JsonResponse($dishVariation, \Symfony\Component\HttpFoundation\Response::HTTP_OK);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['message' => $e->getMessage()], \Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Deletes a dish variation.
     */
    public function delete(DishVariation $dishVariation): JsonResponse
    {
        try {
            // hide the dish variation if it has been assigned to a meal, else delete it
            if (true === $this->dishRepository->hasDishAssociatedMeals($dishVariation)) {
                $dishVariation->setEnabled(false);
                $this->em->persist($dishVariation);
            } else {
                $this->em->remove($dishVariation);
            }
            $this->em->flush();

            return new JsonResponse(null, \Symfony\Component\HttpFoundation\Response::HTTP_OK);
        } catch (Exception $e) {
            $this->logException($e);

            return new JsonResponse(['message' => $e->getMessage()], \Symfony\Component\HttpFoundation\Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
