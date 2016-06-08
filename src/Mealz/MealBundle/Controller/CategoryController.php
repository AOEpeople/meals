<?php

namespace Mealz\MealBundle\Controller;

use Mealz\MealBundle\Entity\Category;
use Symfony\Component\Form\FormInterface;
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

        return $this->renderCategoryList();
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

        return new JsonResponse($this->getRenderedCategoryForm($category, $action, true));
    }

    private function getRenderedCategoryForm(Category $category, $action, $wrapInTr = false)
    {
        $form = $this->createForm(new CategoryForm(), $category, array(
            'action' => $action,
        ));

        if ($wrapInTr) {
            $template = "MealzMealBundle:Category/partials:formTable.html.twig";
        } else {
            $template = "MealzMealBundle:Category/partials:form.html.twig";
        }

        $renderedForm = $this->render($template, array('form' => $form->createView()));

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
            } else {
                return $this->renderCategoryList(array(
                    'form' => $form->createView()
                ));
            }
        }

        return $this->redirectToRoute('MealzMealBundle_Category');
    }

    private function renderCategoryList($parameters = array())
    {
        $categories = $this->getCategoryRepository()->findAll();

        $defaultParameters = array(
            'categories' => $categories
        );

        $mergedParameters = array_merge($defaultParameters, $parameters);

        return $this->render('MealzMealBundle:Category:list.html.twig', $mergedParameters);
    }
}