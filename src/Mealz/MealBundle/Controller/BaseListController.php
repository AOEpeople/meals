<?php

namespace Mealz\MealBundle\Controller;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

abstract class BaseListController extends BaseController
{
    /**
     * @var EntityRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $entityFormName;

    /**
     * @var string
     */
    private $entityName;

    /**
     * @var string
     */
    private $entityClassPath;

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

        $translator = $this->get('translator');
        $translatedEntityName = $translator->trans("entity.$this->entityName", [], 'messages');
        $message = $translator->trans(
            'entity.added',
            array(
                '%entityName%' => $translatedEntityName,
            ),
            'messages'
        );
        return $this->entityFormHandling($request, new $this->entityClassPath, $message);
    }

    public function editAction(Request $request, $slug)
    {
        if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        $entity = $this->findBySlugOrThrowException($slug);

        $translator = $this->get('translator');
        $translatedEntityName = $translator->trans("entity.$this->entityName", [], 'messages');
        $message = $translator->trans(
            'entity.modified',
            array(
                '%entityName%' => $translatedEntityName,
            ),
            'messages'
        );
        return $this->entityFormHandling($request, $entity, $message);
    }

    public function deleteAction($slug)
    {
        if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        $entity = $this->findBySlugOrThrowException($slug);

        $em = $this->getDoctrine()->getManager();
        $em->remove($entity);
        $em->flush();

        $translator = $this->get('translator');
        $translatedEntityName = $translator->trans("entity.$this->entityName", [], 'messages');
        $message = $this->translator->trans(
            'entity.deleted',
            array(
                '%entityName%' => $translatedEntityName,
                '%entity%' => $entity->getTitle(),
            ),
            'messages'
        );
        $this->addFlashMessage($message, 'success');

        return $this->redirectToRoute('MealzMealBundle_' . $this->entityName);
    }

    public function getEmptyFormAction()
    {
        if (!$this->getUser()) {
            return $this->ajaxSessionExpiredRedirect();
        }

        if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        $entity = new $this->entityClassPath();
        $action = $this->generateUrl('MealzMealBundle_' . $this->entityName . '_new');

        return new JsonResponse($this->getRenderedEntityForm($entity, $action));
    }

    public function getPreFilledFormAction($slug)
    {
        if (!$this->getUser()) {
            return $this->ajaxSessionExpiredRedirect();
        }

        if (!$this->get('security.context')->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        $entity = $this->repository->findOneBy(array('slug' => $slug));

        if (!$entity) {
            return new JsonResponse(null, 404);
        }

        $action = $this->generateUrl('MealzMealBundle_' . $this->entityName . '_edit', array('slug' => $slug));

        return new JsonResponse($this->getRenderedEntityForm($entity, $action, true));
    }

    private function getRenderedEntityForm($entity, $action, $wrapInTr = false)
    {
        $form = $this->createForm($this->getNewForm(), $entity, array(
            'action' => $action,
        ));

        if ($wrapInTr) {
            $template = "MealzMealBundle:$this->entityName/partials:formTable.html.twig";
        } else {
            $template = "MealzMealBundle:$this->entityName/partials:form.html.twig";
        }

        $renderedForm = $this->render($template, array('form' => $form->createView()));

        return $renderedForm->getContent();
    }

    private function entityFormHandling(Request $request, $entity, $successMessage)
    {
        $form = $this->createForm($this->getNewForm(), $entity);

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

        return $this->redirectToRoute('MealzMealBundle_' . $this->entityName);
    }

    protected function renderEntityList($parameters = array())
    {
        $entities = $this->getEntities();

        $defaultParameters = array(
            'entities' => $entities
        );

        $mergedParameters = array_merge($defaultParameters, $parameters);

        return $this->render('MealzMealBundle:' . $this->entityName . ':list.html.twig', $mergedParameters);
    }

    protected function getEntities() {
        return $this->repository->findAll();
    }

    /**
     * @param $slug
     *
     * @return object
     *
     * @throws NotFoundHttpException if entity not found
     */
    protected function findBySlugOrThrowException($slug)
    {
        $entity = $this->repository->findOneBy(array('slug' => $slug));
        if (null === $entity) {
            throw $this->createNotFoundException();
        }
        return $entity;
    }

    protected function getNewForm() {
        return new $this->entityFormName();
    }
}