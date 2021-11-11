<?php

namespace App\Mealz\MealBundle\Controller;

use Doctrine\ORM\EntityManager;
use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishRepository;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class DishController extends BaseListController
{
    private DishRepository $dishRepository;

    public function __construct(DishRepository $repository)
    {
        $this->dishRepository = $repository;
        $this->setRepository($repository);
        $this->setEntityName('Dish');
    }

    public function deleteAction($slug): RedirectResponse
    {
        if (!$this->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        /** @var Dish $dish */
        $dish = $this->dishRepository->findOneBy(array('slug' => $slug));

        if (!$dish) {
            throw $this->createNotFoundException();
        }

        /** @var EntityManager $entityManager */
        $entityManager = $this->getDoctrine()->getManager();

        if ($this->dishRepository->hasDishAssociatedMeals($dish)) {
            // if there are meals assigned: just hide this record, but do not delete it
            $dish->setEnabled(false);

            $entityManager->persist($dish);
            $entityManager->flush();
            $message = $this->get('translator')->trans(
                'dish.hidden',
                array('%dish%' => $dish->getTitle()),
                'messages'
            );
            $this->addFlashMessage($message, 'success');
        } else {
            // else: no need to keep an unused record
            $entityManager->remove($dish);
            $entityManager->flush();

            $message = $this->get('translator')->trans(
                'dish.deleted',
                array('%dish%' => $dish->getTitle()),
                'messages'
            );
            $this->addFlashMessage($message, 'success');
        }

        return $this->redirectToRoute('MealzMealBundle_Dish');
    }

    protected function getEntities(): array
    {
        $parameters = array(
            'load_category' => true,
            'load_variations' => true,
            'orderBy_category' => false,
        );

        $dishesQueryBuilder = $this->dishRepository->getSortedDishesQueryBuilder($parameters);

        return $dishesQueryBuilder->getQuery()->execute();
    }
}
