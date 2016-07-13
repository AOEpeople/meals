<?php

namespace Mealz\MealBundle\Controller;

use Doctrine\ORM\EntityManager;
use Mealz\MealBundle\Entity\Dish;
use Mealz\MealBundle\Entity\DishRepository;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DishController extends BaseListController {
    /** @var  DishRepository $repository */
    protected $repository;

    public function deleteAction($slug)
    {
        if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        /** @var DishRepository $dishRepository */
        $dishRepository = $this->getDoctrine()->getRepository('MealzMealBundle:Dish');

        /** @var Dish $dish */
        $dish = $dishRepository->findOneBy(array('slug' => $slug));

        if (!$dish) {
            throw $this->createNotFoundException();
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        if ($dishRepository->hasDishAssociatedMeals($dish)) {
            // if there are meals assigned: just hide this record, but do not delete it
            $dish->setEnabled(false);
            $em->persist($dish);
            $em->flush();
            $this->addFlashMessage(sprintf('Dish "%s" has been hidden.', $dish->getTitle()), 'success');
        } else {
            // else: no need to keep an unused record
            $em->remove($dish);
            $em->flush();

            $this->addFlashMessage(sprintf('Dish "%s" has been deleted.', $dish->getTitle()), 'success');
        }

        return $this->redirectToRoute('MealzMealBundle_Dish');
    }

    protected function getEntities()
    {
        $parameters = array(
            'load_category' => true,
            'orderBy_category' => false
        );

        $dishesQueryBuilder = $this->repository->getSortedDishesQueryBuilder($parameters);

        return $dishesQueryBuilder->getQuery()->execute();
    }

    protected function getNewForm()
    {
        return new $this->entityFormName($this->getParameter('mealz.meal.price'));
    }
}