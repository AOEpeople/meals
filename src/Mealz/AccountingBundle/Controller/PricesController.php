<?php

declare(strict_types=1);

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
        $data = json_decode($request->getContent(), true) ?? [];

        $priceValidation = $this->validateYearAndPrices($data, $priceRepository);
        if (null !== $priceValidation) {
            return $priceValidation;
        }

        $year = (int) $data['year'];
        $price = (float) $data['price'];
        $priceCombined = (float) $data['price_combined'];

        // validation: year already exists
        $existingPrice = $priceRepository->findByYear($year);
        if (null !== $existingPrice) {
            return new JsonResponse(['error' => '1003: Prices for this year already exists.'], Response::HTTP_CONFLICT);
        }

        $priceEntity = Price::create($year, $price, $priceCombined);
        $entityManager->persist($priceEntity);
        $entityManager->flush();

        return new JsonResponse(
            ['message' => 'Price created successfully.', 'price' => $priceEntity->jsonSerialize()],
            Response::HTTP_CREATED
        );
    }

    public function delete(int $year, PriceRepository $priceRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $yearValidation = $this->validateYear(['year' => $year]);
        if (null !== $yearValidation) {
            return $yearValidation;
        }

        $price = $priceRepository->findByYear($year);
        if (null === $price) {
            return new JsonResponse(['error' => '1012: Price not found.'], Response::HTTP_NOT_FOUND);
        }

        $entityManager->remove($price);
        $entityManager->flush();

        return new JsonResponse(['message' => 'Price deleted.'], Response::HTTP_OK);
    }

    public function edit(int $year, Request $request, PriceRepository $priceRepository, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true) ?? [];

        $priceValidation = $this->validateYearAndPrices(array_merge($data, ['year' => $year]), $priceRepository);
        if (null !== $priceValidation) {
            return $priceValidation;
        }

        $price = (float) $data['price'];
        $priceCombined = (float) $data['price_combined'];

        // validation: year already exists
        $existingPrice = $priceRepository->findByYear($year);
        if (null === $existingPrice) {
            return new JsonResponse(['error' => '1023: No price for this year found.'], Response::HTTP_NOT_FOUND);
        }

        // update and persist price
        $existingPrice->setPriceValue($price);
        $existingPrice->setPriceCombinedValue($priceCombined);

        $entityManager->persist($existingPrice);
        $entityManager->flush();

        return new JsonResponse(
            ['message' => 'Price updated successfully', 'price' => $existingPrice->jsonSerialize()],
            Response::HTTP_OK
        );
    }

    private function validateYear($data): ?JsonResponse
    {
        // json validation
        if (null === $data) {
            return new JsonResponse(['error' => '1010: Invalid JSON data.'], Response::HTTP_BAD_REQUEST);
        }

        // validation: required year field
        if (!isset($data['year'])) {
            return new JsonResponse(['error' => '1011: Missing required field: year.'], Response::HTTP_BAD_REQUEST);
        }

        // validation: positive and later date
        if ($data['year'] <= 2000) {
            return new JsonResponse(['error' => '1022: Year must be a positive integer, later then 2000.'], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    private function validateYearAndPrices($data, PriceRepository $priceRepository): ?JsonResponse
    {
        $response = $this->validatePrice($data);
        if (!is_null($response)) {
            return $response;
        }

        return $this->validatePricesForYear($data, $priceRepository);
    }

    private function validatePrice($data): ?JsonResponse
    {
        $yearValidation = $this->validateYear($data);
        if (null !== $yearValidation) {
            return $yearValidation;
        }

        // validation: required fields
        if (!isset($data['price']) || !isset($data['price_combined'])) {
            return new JsonResponse(['error' => '1001: Missing required fields: year, price, price_combined.'], Response::HTTP_BAD_REQUEST);
        }

        return null;
    }

    private function validatePricesForYear($data, PriceRepository $priceRepository): ?JsonResponse
    {
        $year = (int) $data['year'];
        $price = (float) $data['price'];
        $priceCombined = (float) $data['price_combined'];

        // validation: prices have to be higher or equal to last year
        $previousPrice = $priceRepository->findByYear($year - 1);
        if (null !== $previousPrice) {
            if ($price < $previousPrice->getPriceValue()) {
                return new JsonResponse(['error' => '1004: Price cannot be lower than previous year.'], Response::HTTP_BAD_REQUEST);
            }
            if ($priceCombined < $previousPrice->getPriceCombinedValue()) {
                return new JsonResponse(['error' => '1005: Combined price cannot be lower than previous year.'], Response::HTTP_BAD_REQUEST);
            }
        }

        $nextPrice = $priceRepository->findByYear($year + 1);
        if (null !== $nextPrice) {
            if ($price > $nextPrice->getPriceValue()) {
                return new JsonResponse(['error' => '1006: Price cannot be higher than next year.'], Response::HTTP_BAD_REQUEST);
            }
            if ($priceCombined > $nextPrice->getPriceCombinedValue()) {
                return new JsonResponse(['error' => '1007: Combined price cannot be higher than next year.'], Response::HTTP_BAD_REQUEST);
            }
        }

        return null;
    }
}
