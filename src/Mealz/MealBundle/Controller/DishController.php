<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Repository\CategoryRepository;
use App\Mealz\MealBundle\Repository\CategoryRepositoryInterface;
use App\Mealz\MealBundle\Repository\DishRepository;
use App\Mealz\MealBundle\Service\Logger\MealsLoggerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Security("is_granted('ROLE_KITCHEN_STAFF')")
 */
class DishController extends BaseListController
{
    private float $defaultPrice;
    private CategoryRepositoryInterface $categoryRepository;
    private DishRepository $dishRepository;
    private EntityManagerInterface $em;

    public function __construct(
        float $price,
        CategoryRepository $categoryRepository,
        DishRepository $dishRepository,
        EntityManagerInterface $em
    ) {
        $this->defaultPrice = $price;
        $this->dishRepository = $dishRepository;
        $this->em = $em;
    }

//    /**
//     * @return RedirectResponse|Response
//     */
//    public function editAction(Request $request, Dish $dish)
//    {
//        $form = $this->createForm(DishForm::class, $dish);
//
//        // handle form submission
//        if ($request->isMethod('POST')) {
//            $form->handleRequest($request);
//
//            if ($form->isValid()) {
//                /** @var DishVariation $variation */
//                foreach ($dish->getVariations() as $variation) {
//                    $variation->setOneServingSize($dish->hasOneServingSize());
//                }
//                $entityManager = $this->getDoctrine()->getManager();
//                $entityManager->persist($dish);
//                $entityManager->flush();
//
//                $translator = $this->get('translator');
//                $translatedEntityName = $translator->trans('entity.Dish', [], 'messages');
//                $message = $translator->trans(
//                    'entity.modified',
//                    ['%entityName%' => $translatedEntityName],
//                    'messages'
//                );
//                $this->addFlashMessage($message, 'success');
//            } else {
//                return $this->renderEntityList([
//                    'form' => $form->createView(),
//                ]);
//            }
//        }
//
//        return $this->redirectToRoute('MealzMealBundle_Dish');
//    }

    // public function deleteAction(
    //     Dish $dish,
    //     MealsLoggerInterface $logger,
    //     TranslatorInterface $translator
    // ): RedirectResponse {
    //     try {
    //         /** @var EntityManager $entityManager */
    //         $entityManager = $this->getDoctrine()->getManager();

    //         // hide the dish if it has been assigned to a meal, else delete it
    //         if (true === $this->dishRepository->hasDishAssociatedMeals($dish)) {
    //             $dish->setEnabled(false);
    //             $entityManager->persist($dish);
    //             $message = $translator->trans('dish.hidden', ['%dish%' => $dish->getTitle()], 'messages');
    //         } else {
    //             $entityManager->remove($dish);
    //             $message = $translator->trans('dish.deleted', ['%dish%' => $dish->getTitle()], 'messages');
    //         }

    //         $entityManager->flush();
    //         $this->addFlashMessage($message, 'success');
    //     } catch (Exception $e) {
    //         $logger->logException($e, 'dish delete error');
    //         $message = $translator->trans('dish.delete_error', ['%dish%' => $dish->getTitle()], 'messages');
    //         $this->addFlashMessage($message, 'danger');
    //     }

    //     return $this->redirectToRoute('MealzMealBundle_Dish');
    // }

    public function getDishes(): JsonResponse
    {
        $dishes = $this->dishRepository->findBy(['parent' => null, 'enabled' => true]);

        return new JsonResponse($dishes, 200);
    }

    public function new(Request $request): JsonResponse
    {
        try {
            $parameters = json_decode($request->getContent(), true);
            if (!isset($parameters['titleDe']) || !isset($parameters['titleEn']) || !isset($parameters['oneServingSize'])) {
                throw new Exception('Missing parameters');
            }

            $dish = new Dish();
            $dish->setTitleDe($parameters['titleDe']);
            $dish->setTitleEn($parameters['titleEn']);
            $dish->setOneServingSize($parameters['oneServingSize']);
            $dish->setPrice($this->defaultPrice);

            if (isset($parameters['descriptionDe']) && null !== $parameters['descriptionDe']) {
                $dish->setDescriptionDe($parameters['descriptionDe']);
            }
            if (isset($parameters['descriptionEn']) && null !== $parameters['descriptionEn']) {
                $dish->setDescriptionEn($parameters['descriptionEn']);
            }
            if (isset($parameters['category'])  && null !== $parameters['category']) {
                $dish->setCategory($this->categoryRepository->find($parameters['category']));
            }

            $this->em->persist($dish);
            $this->em->flush();

            return new JsonResponse(['status' => 'success'], 200);
        } catch (Exception $e) {
            $this->logException($e);
            return new JsonResponse(['status' => $e->getMessage()], 500);
        }
    }

    public function delete(Dish $dish)
    {
        try {
             // hide the dish if it has been assigned to a meal, else delete it
            if ($this->dishRepository->hasDishAssociatedMeals($dish)) {
                $dish->setEnabled(false);
                $this->em->persist($dish);
            } else {
                $this->em->remove($dish);
                $this->em->flush();
            }

            return new JsonResponse(['status' => 'success'], 200);
        } catch (Exception $e) {
            $this->logException($e);
            return new JsonResponse(['status' => $e->getMessage()], 500);
        }
    }

    protected function getEntities(): array
    {
        $parameters = [
            'load_category' => true,
            'load_variations' => true,
            'orderBy_category' => false,
        ];

        $dishesQueryBuilder = $this->dishRepository->getSortedDishesQueryBuilder($parameters);

        return $dishesQueryBuilder->getQuery()->execute();
    }
}
