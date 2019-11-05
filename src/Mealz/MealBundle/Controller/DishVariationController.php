<?php

namespace Mealz\MealBundle\Controller;

use Doctrine\ORM\EntityManager;
use Mealz\MealBundle\Entity\DishVariation;
use Mealz\MealBundle\Form\DishVariationForm;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Dish variation controller.
 *
 * @package Mealz\MealBundle\Controller
 * @author Chetan Thapliyal <chetan.thapliyal@aoe.com>
 */
class DishVariationController extends BaseController
{
    /**
     * @param  Request $request
     * @param  integer $slug
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction(Request $request, $slug)
    {
        $this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

        /** @var \Mealz\MealBundle\Entity\Dish $dish */
        $dish = $this->getDishRepository()->find($slug);

        if ($dish === null) {
            throw $this->createNotFoundException();
        }

        $dishVariation = new DishVariation();
        $dishVariation->setParent($dish);
        $dishVariationForm = $this->createForm(
            $this->getNewForm(),
            $dishVariation,
            ['action' => $this->generateUrl('MealzMealBundle_DishVariation_new', ['slug' => $dish->getId()])]
        );
        $dishVariationForm->handleRequest($request);

        if ($dishVariationForm->isSubmitted() && $dishVariationForm->isValid()) {
            $dishVariation = $dishVariationForm->getData();
            $this->persistEntity($dishVariation);

            $message = $this->get('translator')->trans(
                'entity.added',
                array('%entityName%' => $dishVariation->getTitle()),
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
     * The edit Action
     * @param Request $request
     * @param String $slug
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function editAction(Request $request, $slug)
    {
        $this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

        /** @var \Mealz\MealBundle\Entity\DishVariationRepository $dishVariationRepo */
        $dishVariationRepo = $this->getDoctrine()->getRepository('MealzMealBundle:DishVariation');

        /** @var \Mealz\MealBundle\Entity\DishVariation $dish */
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
                array('%entityName%' => $dishVariation->getTitle()),
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
     * the delete Action
     * @param  integer $slug
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAction($slug)
    {
        $this->denyAccessUnlessGranted('ROLE_KITCHEN_STAFF');

        /** @var \Mealz\MealBundle\Entity\DishVariationRepository $dishRepository */
        if (is_object($this->getDoctrine()->getRepository('MealzMealBundle:DishVariation')) === true) {
            $dishVariationRepo = $this->getDoctrine()->getRepository('MealzMealBundle:DishVariation');
        }

        /** @var \Mealz\MealBundle\Entity\DishVariation $dishVariation */
        $dishVariation = $dishVariationRepo->find($slug);

        if ($dishVariation === null) {
            throw $this->createNotFoundException();
        }

        /** @var EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        if ($dishVariationRepo->hasDishAssociatedMeals($dishVariation) === true) {
            // if there are meals assigned: just hide this record, but do not delete it
            $dishVariation->setEnabled(false);
            $entityManager->persist($dishVariation);
            $entityManager->flush();
            $message = $this->get('translator')->trans(
                'dish_variation.hidden',
                array('%dishVariation%' => $dishVariation->getTitle()),
                'messages'
            );
            $this->addFlashMessage($message, 'success');
        } else {
            // else: no need to keep an unused record
            $entityManager->remove($dishVariation);
            $entityManager->flush();

            $message = $this->get('translator')->trans(
                'dish_variation.deleted',
                array('%dishVariation%' => $dishVariation->getTitle()),
                'messages'
            );
            $this->addFlashMessage($message, 'success');
        }

        return $this->redirectToRoute('MealzMealBundle_Dish');
    }

    /**
     * get the new Form
     * @return object
     */
    protected function getNewForm()
    {
        return $this->get('mealz_meal.form.dishvariation');
    }

    /**
     * persist the Entity
     * @param $entity
     */
    private function persistEntity($entity)
    {
        /** @var EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($entity);
        $entityManager->flush();
    }
}
