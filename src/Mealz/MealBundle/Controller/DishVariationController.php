<?php

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Repository\DishRepository;
use App\Mealz\MealBundle\Service\Logger\MealsLoggerInterface;
use Doctrine\ORM\EntityManager;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Security("is_granted('ROLE_KITCHEN_STAFF')")
 */
class DishVariationController extends BaseController
{
//    public function new(Request $request, Dish $dish): Response
//    {
//        $dishVariation = new DishVariation();
//        $dishVariation->setParent($dish);
//        $dishVariation->setOneServingSize($dish->hasOneServingSize());
//
//        $dishVariationForm = $this->createForm(
//            $this->getNewForm(),
//            $dishVariation,
//            ['action' => $this->generateUrl('MealzMealBundle_DishVariation_new', ['id' => $dish->getId()])]
//        );
//        $dishVariationForm->handleRequest($request);
//
//        if ($dishVariationForm->isSubmitted() && $dishVariationForm->isValid()) {
//            $dishVariation = $dishVariationForm->getData();
//            $this->persistEntity($dishVariation);
//
//            $message = $this->get('translator')->trans(
//                'entity.added',
//                ['%entityName%' => $dishVariation->getTitle()],
//                'messages'
//            );
//            $this->addFlashMessage($message, 'success');
//
//            return $this->redirectToRoute('MealzMealBundle_Dish');
//        }
//
//        $renderedForm = $this->render('MealzMealBundle:DishVariation:new.html.twig', [
//            'form' => $dishVariationForm->createView(),
//            'dishVariation' => $dishVariation,
//        ]);
//
//        return new Response($renderedForm->getContent());
//    }

//    /**
//     * @param string $slug
//     *
//     * @return Response|RedirectResponse
//     */
//    public function edit(Request $request, $slug, DishVariationRepositoryInterface $dishVariationRepo)
//    {
//        /** @var \App\Mealz\MealBundle\Entity\DishVariation $dish */
//        $dishVariation = $dishVariationRepo->find($slug);
//
//        if (!$dishVariation) {
//            throw $this->createNotFoundException();
//        }
//
//        $dishVariationForm = $this->createForm(
//            $this->getNewForm(),
//            $dishVariation,
//            ['action' => $this->generateUrl('MealzMealBundle_DishVariation_edit', ['slug' => $dishVariation->getId()])]
//        );
//        $dishVariationForm->handleRequest($request);
//
//        if ($dishVariationForm->isSubmitted() && $dishVariationForm->isValid()) {
//            $dishVariation = $dishVariationForm->getData();
//            $this->persistEntity($dishVariation);
//
//            $message = $this->get('translator')->trans(
//                'entity.modified',
//                ['%entityName%' => $dishVariation->getTitle()],
//                'messages'
//            );
//            $this->addFlashMessage($message, 'success');
//
//            return $this->redirectToRoute('MealzMealBundle_Dish');
//        }
//
//        $renderedForm = $this->render(
//            'MealzMealBundle:DishVariation:new.html.twig',
//            [
//                'form' => $dishVariationForm->createView(),
//                'dishVariation' => $dishVariation,
//            ]
//        );
//
//        return new Response($renderedForm->getContent());
//    }

    public function deleteAction(
        DishVariation $dishVariation,
        DishRepository $dishRepo,
        MealsLoggerInterface $logger,
        TranslatorInterface $translator
    ): RedirectResponse {
        try {
            /** @var EntityManager $entityManager */
            $entityManager = $this->getDoctrine()->getManager();

            // hide the dish variation if it has been assigned to a meal, else delete it
            if ($dishRepo->hasDishAssociatedMeals($dishVariation)) {
                $dishVariation->setEnabled(false);
                $entityManager->persist($dishVariation);
                $message = $translator->trans('dish_variation.hidden', ['%dishVariation%' => $dishVariation->getTitle()], 'messages');
            } else {
                $entityManager->remove($dishVariation);
                $message = $translator->trans('dish_variation.deleted', ['%dishVariation%' => $dishVariation->getTitle()], 'messages');
            }

            $entityManager->flush();
            $this->addFlashMessage($message, 'success');
        } catch (Exception $e) {
            $logger->logException($e, 'dish delete error');
            $message = $translator->trans('dish_variation.delete_error', ['%dishVariation%' => $dishVariation->getTitle()], 'messages');
            $this->addFlashMessage($message, 'danger');
        }

        return $this->redirectToRoute('MealzMealBundle_Dish');
    }

//    protected function getNewForm()
//    {
//        return DishVariationForm::class;
//    }

//    /**
//     * @param $entity
//     */
//    private function persistEntity($entity): void
//    {
//        /** @var EntityManager $entityManager */
//        $entityManager = $this->getDoctrine()->getManager();
//        $entityManager->persist($entity);
//        $entityManager->flush();
//    }
}
