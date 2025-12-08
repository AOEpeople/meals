<?php

namespace App\Mealz\AccountingBundle\Controller;

use App\Mealz\AccountingBundle\Entity\Price;
use App\Mealz\AccountingBundle\Repository\PriceRepository;
use App\Mealz\MealBundle\Controller\BaseController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(new Expression('is_granted("ROLE_KITCHEN_STAFF") or is_granted("ROLE_FINANCE")'))]
final class PricesController extends BaseController
{
    public function list(PriceRepository $priceRepository): JsonResponse
    {
        $prices = $priceRepository->findAll();

        $pricesData = [];
        foreach ($prices as $price) {
            $pricesData[$price->getYear()] = $price;
        }

        return new JsonResponse(['prices' => $pricesData]);
    }

    public function add(Request $request, PriceRepository $priceRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // json validation
        if (null === $data) {
            return new JsonResponse(['error' => '1000: Invalid JSON data.'], Response::HTTP_BAD_REQUEST);
        }

        // validation: required fields
        if (!isset($data['year']) || !isset($data['price']) || !isset($data['price_combined'])) {
            return new JsonResponse(['error' => '1001: Missing required fields: year, price, price_combined.'], Response::HTTP_BAD_REQUEST);
        }

        $year = (int) $data['year'];
        $price = (float) $data['price'];
        $priceCombined = (float) $data['price_combined'];

        // validation: positive and later date
        if ($year <= 2000) {
            return new JsonResponse(['error' => '1002: Year must be a positive integer, later then 2000.'], Response::HTTP_BAD_REQUEST);
        }

        // validation: year already exists
        $existingPrice = $priceRepository->findByYear($year);
        if (null !== $existingPrice) {
            return new JsonResponse(['error' => '1003: Prices for this year already exists.'], Response::HTTP_CONFLICT);
        }

        // validation: prices have to be higher or equal to last year
        $previousPrice = $priceRepository->findByYear($year - 1);
        if (null !== $previousPrice) {
            if ($price < $previousPrice->getPrice()) {
                return new JsonResponse(['error' => '1004: Price cannot be lower than previous year.'], Response::HTTP_BAD_REQUEST);
            }
            if ($priceCombined < $previousPrice->getPriceCombined()) {
                return new JsonResponse(['error' => '1005: Combined price cannot be lower than previous year.'], Response::HTTP_BAD_REQUEST);
            }
        }

        $priceEntity = Price::create($year, $price, $priceCombined);
        $entityManager->persist($priceEntity);
        $entityManager->flush();

        return new JsonResponse(
            ['message' => 'Price created successfully.', 'price' => $priceEntity->jsonSerialize()],
            Response::HTTP_CREATED
        );
    }

    public function delete(Request $request, PriceRepository $priceRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // json validation
        if (null === $data) {
            return new JsonResponse(['error' => '1010: Invalid JSON data.'], Response::HTTP_BAD_REQUEST);
        }

        // validation: required year field
        if (!isset($data['year'])) {
            return new JsonResponse(['error' => '1011: Missing required field: year.'], Response::HTTP_BAD_REQUEST);
        }

        $price = $priceRepository->findByYear($data['year']);
        if (null === $price) {
            return new JsonResponse(['error' => '1012: Price not found.'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($price);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Price deleted.'], Response::HTTP_OK);
    }
    public function edit(Request $request, PriceRepository $priceRepository, EntityManagerInterface $entityManager): JsonResponse    {
        $data = json_decode($request->getContent(), true);

        // json validation
        if (null === $data) {
            return new JsonResponse(['error' => '1020: Invalid JSON data.'], Response::HTTP_BAD_REQUEST);
        }

        // validation: required fields
        if (!isset($data['year']) || !isset($data['price']) || !isset($data['price_combined'])) {
            return new JsonResponse(['error' => '1021: Missing required fields: year, price, price_combined.'], Response::HTTP_BAD_REQUEST);
        }

        $year = (int) $data['year'];
        $price = (float) $data['price'];
        $priceCombined = (float) $data['price_combined'];

        // validation: positive and later date
        if ($year <= 2000) {
            return new JsonResponse(['error' => '1022: Year must be a positive integer, later then 2000.'], Response::HTTP_BAD_REQUEST);
        }

        // validation: year already exists
        $existingPrice = $priceRepository->findByYear($year);
        if (null === $existingPrice) {
            return new JsonResponse(['error' => '1023: No price for this year found.'], Response::HTTP_CONFLICT);
        }

        // validation: prices have to be higher or equal to last year
        $previousPrice = $priceRepository->findByYear($year - 1);
        if (null !== $previousPrice) {
            if ($price < $previousPrice->getPrice()) {
                return new JsonResponse(['error' => '1024: Price cannot be lower than previous year.'], Response::HTTP_BAD_REQUEST);
            }
            if ($priceCombined < $previousPrice->getPriceCombined()) {
                return new JsonResponse(['error' => '1025: Combined price cannot be lower than previous year.'], Response::HTTP_BAD_REQUEST);
            }
        }

        // update and persist price
        $existingPrice->setPrice($price);
        $existingPrice->setPriceCombined($priceCombined);

        $entityManager->persist($existingPrice);
        $entityManager->flush();

        return new JsonResponse(
            ['message' => 'Price created successfully', 'price' => $existingPrice->jsonSerialize()],
            Response::HTTP_CREATED
        );
    }
}