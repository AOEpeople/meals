<?php

namespace Mealz\MealBundle\Controller;

use Mealz\MealBundle\Entity\Category;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Mealz\MealBundle\Form\CategoryForm;

class CategoryController extends BaseController {
    public function listAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        $categories = $this->getCategoryRepository()->findAll();

        return $this->render('MealzMealBundle:Category:list.html.twig', array(
            'categories' => $categories
        ));
    }

    public function newAction(Request $request)
    {
        if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        return $this->categoryFormHandling($request, new Category(), 'Category has been added.');
    }

    public function editAction(Request $request, $slug)
    {
        if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        /* @var Category $category */
        $category = $this->getCategoryRepository()->findOneBy(array('slug' => $slug));
        if (null === $category) {
            throw $this->createNotFoundException();
        }

        return $this->categoryFormHandling($request, $category, 'Category has been modified.');
    }

    public function deleteAction($slug)
    {
        /** @var Category $category */
        $category = $this->getCategoryRepository()->findOneBy(array(
            'slug' => $slug
        ));

        $em = $this->getDoctrine()->getManager();
        $em->remove($category);
        $em->flush();

        $this->addFlashMessage(sprintf('Deleted category: "%s"', $category->getTitle()), 'success');

        return $this->redirectToRoute('MealzMealBundle_Category');
    }

    public function getEmptyFormAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        $category = new Category();
        $action = $this->generateUrl('MealzMealBundle_Category_new');

        return new JsonResponse($this->getRenderedCategoryForm($category, $action));
    }

    public function getPreFilledFormAction($slug)
    {
        if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        /* @var Category $category */
        $category = $this->getCategoryRepository()->findOneBy(array('slug' => $slug));

        if (!$category) {
            return new JsonResponse(null, 404);
        }

        $action = $this->generateUrl('MealzMealBundle_Category_edit', array('slug' => $slug));

        return new JsonResponse($this->getRenderedCategoryForm($category, $action));
    }

    private function getRenderedCategoryForm(Category $category, $action)
    {
        $form = $this->createForm(new CategoryForm(), $category, array(
            'action' => $action,
        ));

        $renderedForm = $this->render('MealzMealBundle:Category/partials:form.html.twig',
            array('form' => $form->createView()));

        return $renderedForm->getContent();
    }

    private function categoryFormHandling(Request $request, Category $category, $successMessage)
    {
        $form = $this->createForm(new CategoryForm(), $category);

        // handle form submission
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($category);
                $em->flush();

                $this->addFlashMessage($successMessage, 'success');
            }
        }

        return $this->redirectToRoute('MealzMealBundle_Category');
    }
}