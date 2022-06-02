<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Controller;

use App\Mealz\MealBundle\Entity\Dish;
use App\Mealz\MealBundle\Entity\DishRepository;
use App\Mealz\MealBundle\Entity\DishVariation;
use App\Mealz\MealBundle\Form\Dish\DishForm;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Security("is_granted('ROLE_KITCHEN_STAFF')")
 */
class DishController extends BaseListController
{
    private DishRepository $dishRepository;

    public function __construct(DishRepository $repository)
    {
        $this->dishRepository = $repository;
        $this->setRepository($repository);
        $this->setEntityName('Dish');
    }

    /**
     * @return RedirectResponse|Response
     */
    public function editAction(Request $request, Dish $dish)
    {
        $form = $this->createForm(DishForm::class, $dish);

        // handle form submission
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                /** @var DishVariation $variation */
                foreach ($dish->getVariations() as $variation) {
                    $variation->setOneServingSize($dish->hasOneServingSize());
                }
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($dish);
                $entityManager->flush();

                $translator = $this->get('translator');
                $translatedEntityName = $translator->trans('entity.Dish', [], 'messages');
                $message = $translator->trans(
                    'entity.modified',
                    ['%entityName%' => $translatedEntityName],
                    'messages'
                );
                $this->addFlashMessage($message, 'success');
            } else {
                return $this->renderEntityList([
                    'form' => $form->createView(),
                ]);
            }
        }

        return $this->redirectToRoute('MealzMealBundle_Dish');
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
