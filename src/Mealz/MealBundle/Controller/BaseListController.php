<?php
/**
 * Created by PhpStorm.
 * User: jonathan.klauck
 * Date: 08.06.2016
 * Time: 17:46
 */

namespace Mealz\MealBundle\Controller;

use Doctrine\ORM\EntityRepository;
use Mealz\MealBundle\Entity\Category;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

abstract class BaseListController extends BaseController
{
    /**
     * @var EntityRepository
     */
    private $repository;

    /**
     * @var string
     */
    private $entityName;

    /**
     * @var string
     */
    private $entityClassPath;

    /**
     * @var string
     */
    private $entityFormName;

    public function setEntityName($entityName)
    {
        $this->entityName = $entityName;
        $this->entityClassPath = '\Mealz\MealBundle\Entity\\' . $entityName;
        $this->entityFormName = '\Mealz\MealBundle\Form\\' . $entityName . 'Form';
    }

    public function setRepository(EntityRepository $repository)
    {
        $this->repository = $repository;
    }

    public function listAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        return $this->renderEntityList();
    }

    public function newAction(Request $request)
    {
        if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        return $this->categoryFormHandling(
            $request,
            new $this->entityClassPath,
            $this->entityName . ' has been added.'
        );
    }

    public function editAction(Request $request, $slug)
    {
        if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        $entity = $this->repository->findOneBy(array('slug' => $slug));
        if (null === $entity) {
            throw $this->createNotFoundException();
        }

        return $this->categoryFormHandling($request, $entity, $this->entityName . ' has been modified.');
    }

    public function deleteAction($slug)
    {
        $entity = $this->repository->findOneBy(array(
            'slug' => $slug
        ));

        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();

        $this->addFlashMessage(sprintf('Deleted category: "%s"', $entity->getTitle()), 'success');

        return $this->redirectToRoute('MealzMealBundle_' . $this->entityName);
    }

    public function getEmptyFormAction()
    {
        if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        $entity = new $this->entityClassPath();
        $action = $this->generateUrl('MealzMealBundle_' . $this->entityName . '_new');

        return new JsonResponse($this->getRenderedCategoryForm($entity, $action));
    }

    public function getPreFilledFormAction($slug)
    {
        if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        /* @var Category $entity */
        $entity = $this->repository->findOneBy(array('slug' => $slug));

        if (!$entity) {
            return new JsonResponse(null, 404);
        }

        $action = $this->generateUrl('MealzMealBundle_' . $this->entityName . '_edit', array('slug' => $slug));

        return new JsonResponse($this->getRenderedCategoryForm($entity, $action, true));
    }

    private function getRenderedCategoryForm($entity, $action, $wrapInTr = false)
    {
        $form = $this->createForm(new $this->entityFormName(), $entity, array(
            'action' => $action,
        ));

        /** @TODO */
        if ($wrapInTr) {
            $template = "MealzMealBundle:$this->entityName/partials:formTable.html.twig";
        } else {
            $template = "MealzMealBundle:$this->entityName/partials:form.html.twig";
        }

        $renderedForm = $this->render($template, array('form' => $form->createView()));

        return $renderedForm->getContent();
    }

    private function categoryFormHandling(Request $request, $entity, $successMessage)
    {
        /** @TODO */
        $form = $this->createForm(new $this->entityFormName(), $entity);

        // handle form submission
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $em = $this->getDoctrine()->getManager();
                $em->persist($entity);
                $em->flush();

                $this->addFlashMessage($successMessage, 'success');
            } else {
                return $this->renderEntityList(array(
                    'form' => $form->createView()
                ));
            }
        }

        /** @TODO */
        return $this->redirectToRoute('MealzMealBundle_' . $this->entityName);
    }

    private function renderEntityList($parameters = array())
    {
        $entities = $this->repository->findAll();

        $defaultParameters = array(
            'entities' => $entities
        );

        $mergedParameters = array_merge($defaultParameters, $parameters);

        /** @TODO */
        return $this->render('MealzMealBundle:' . $this->entityName . ':list.html.twig', $mergedParameters);
    }
}