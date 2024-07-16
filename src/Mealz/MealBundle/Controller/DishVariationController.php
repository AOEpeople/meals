<?php

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Enum\Diet;
use App\Mealz\MealBundle\Repository\DishRepositoryInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_KITCHEN_STAFF')]
class DishVariationController extends BaseController
{
    public function __construct(
        private readonly float $price,
        private readonly DishRepositoryInterface $dishRepository,
        private readonly EntityManagerInterface $em,
        private readonly LoggerInterface $logger,
    ) {
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
            $dishVariation->setPrice($this->price);
            $dishVariation->setCategory($dish->getCategory());

            if (true === isset($parameters['titleDe']) && true === isset($parameters['titleEn'])) {
                $dishVariation->setTitleDe($parameters['titleDe']);
                $dishVariation->setTitleEn($parameters['titleEn']);
            } else {
                throw new Exception('202: Title not set');
            }

            if (true === isset($parameters['diet'])) {
                $dishVariation->setDiet(Diet::tryFrom($parameters['diet']));
            } else {
                $dishVariation->setDiet(Diet::tryFrom('meat'));
            }

            $this->em->persist($dishVariation);
            $this->em->flush();

            return new JsonResponse(null, Response::HTTP_OK);
        } catch (Exception $e) {
            $this->logger->error('dish variation create error', $this->getTrace($e));

            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
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
            if (true === isset($parameters['diet'])) {
                $dishVariation->setDiet(Diet::tryFrom($parameters['diet']));
            }


            $this->em->persist($dishVariation);
            $this->em->flush();

            return new JsonResponse($dishVariation, Response::HTTP_OK);
        } catch (Exception $e) {
            $this->logger->error('dish variation update error', $this->getTrace($e));

            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
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

            return new JsonResponse(null, Response::HTTP_OK);
        } catch (Exception $e) {
            $this->logger->error('dish variation delete error', $this->getTrace($e));

            return new JsonResponse(['message' => $e->getMessage()], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }
}
