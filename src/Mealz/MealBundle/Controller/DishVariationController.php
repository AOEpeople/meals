<?php

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Form\Dish\DishVariationForm;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class DishVariationController extends BaseController
{
    /**
     * @Security("is_granted('ROLE_KITCHEN_STAFF')")
     */
    public function new(Request $request, Dish $dish): Response
    {
        $dishVariation = new DishVariation();
        $dishVariation->setParent($dish);
        $dishVariationForm = $this->createForm(
            $this->getNewForm(),
            $dishVariation,
            ['action' => $this->generateUrl('MealzMealBundle_DishVariation_new', ['id' => $dish->getId()])]
        );
        $dishVariationForm->handleRequest($request);

        if ($dishVariationForm->isSubmitted() && $dishVariationForm->isValid()) {
            $dishVariation = $dishVariationForm->getData();
            $this->persistEntity($dishVariation);

            $message = $this->get('translator')->trans(
                'entity.added',
                ['%entityName%' => $dishVariation->getTitle()],
                'messages'
            );
            $this->addFlashMessage($message, 'success');

            return $this->redirectToRoute('MealzMealBundle_Dish');
        }

        $renderedForm = $this->render('MealzMealBundle:DishVariation:new.html.twig', [
            'form' => $dishVariationForm->createView(),
            'dishVariation' => $dishVariation,
        ]);

        return new JsonResponse($renderedForm->getContent());
    }

    /**
     * @param string $slug
     *
     * @return JsonResponse|RedirectResponse
     */
    public function edit(Request $request, $slug)
    {
        $this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

        /** @var \App\Mealz\MealBundle\Entity\DishVariationRepository $dishVariationRepo */
        $dishVariationRepo = $this->getDoctrine()->getRepository(DishVariation::class);

        /** @var \App\Mealz\MealBundle\Entity\DishVariation $dish */
        $dishVariation = $dishVariationRepo->find($slug);

        if (!$dishVariation) {
            throw $this->createNotFoundException();
        }

        $dishVariationForm = $this->createForm(
            $this->getNewForm(),
            $dishVariation,
            ['action' => $this->generateUrl('MealzMealBundle_DishVariation_edit', ['slug' => $dishVariation->getId()])]
        );
        $dishVariationForm->handleRequest($request);

        if ($dishVariationForm->isSubmitted() && $dishVariationForm->isValid()) {
            $dishVariation = $dishVariationForm->getData();
            $this->persistEntity($dishVariation);

            $message = $this->get('translator')->trans(
                'entity.modified',
                ['%entityName%' => $dishVariation->getTitle()],
                'messages'
            );
            $this->addFlashMessage($message, 'success');

            return $this->redirectToRoute('MealzMealBundle_Dish');
        }

        $renderedForm = $this->render(
            'MealzMealBundle:DishVariation:new.html.twig',
            [
                'form' => $dishVariationForm->createView(),
                'dishVariation' => $dishVariation,
            ]
        );

        return new JsonResponse($renderedForm->getContent());
    }

    /**
     * @param int $slug
     */
    public function delete($slug): RedirectResponse
    {
        $this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

        /* @var \App\Mealz\MealBundle\Entity\DishVariationRepository $dishRepository */
        if (true === is_object($this->getDoctrine()->getRepository(DishVariation::class))) {
            $dishVariationRepo = $this->getDoctrine()->getRepository(DishVariation::class);
        }

        /** @var \App\Mealz\MealBundle\Entity\DishVariation $dishVariation */
        $dishVariation = $dishVariationRepo->find($slug);

        if (null === $dishVariation) {
            throw $this->createNotFoundException();
        }

        /** @var EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        if (true === $dishVariationRepo->hasDishAssociatedMeals($dishVariation)) {
            // if there are meals assigned: just hide this record, but do not delete it
            $dishVariation->setEnabled(false);
            $entityManager->persist($dishVariation);
            $entityManager->flush();
            $message = $this->get('translator')->trans(
                'dish_variation.hidden',
                ['%dishVariation%' => $dishVariation->getTitle()],
                'messages'
            );
            $this->addFlashMessage($message, 'success');
        } else {
            // else: no need to keep an unused record
            $entityManager->remove($dishVariation);
            $entityManager->flush();

            $message = $this->get('translator')->trans(
                'dish_variation.deleted',
                ['%dishVariation%' => $dishVariation->getTitle()],
                'messages'
            );
            $this->addFlashMessage($message, 'success');
        }

        return $this->redirectToRoute('MealzMealBundle_Dish');
    }

    protected function getNewForm()
    {
        return DishVariationForm::class;
    }

    /**
     * @param $entity
     */
    private function persistEntity($entity): void
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($entity);
        $entityManager->flush();
    }
}
