<?php

namespace App\Mealz\MealBundle\Controller;

use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

abstract class BaseListController extends BaseController
{
    protected ObjectRepository $repository;

    protected string $entityFormName;

    private string $entityName;

    private string $entityClassPath;

    public function setEntityName(string $entityName): void
    {
        $this->entityName = $entityName;
        $this->entityClassPath = 'App\Mealz\MealBundle\Entity\\' . $entityName;
        $this->entityFormName = 'App\Mealz\MealBundle\Form\\' . $entityName . '\\' . $entityName . 'Form';
    }

    public function setRepository(ObjectRepository $repository): void
    {
        $this->repository = $repository;
    }

    /**
     * @return Response
     */
    public function list()
    {
        if (!$this->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        return $this->renderEntityList();
    }

    /**
     * @return RedirectResponse|Response
     */
    public function new(Request $request)
    {
        if (false === $this->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        $translator = $this->get('translator');
        $translatedEntityName = $translator->trans("entity.$this->entityName", [], 'messages');
        $message = $translator->trans(
            'entity.added',
            ['%entityName%' => $translatedEntityName],
            'messages'
        );

        return $this->entityFormHandling($request, new $this->entityClassPath(), $message);
    }

    /**
     * @param $slug
     *
     * @return RedirectResponse|Response
     */
    public function edit(Request $request, $slug)
    {
        if (false === $this->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        $entity = $this->findBySlugOrThrowException($slug);

        $translator = $this->get('translator');
        $translatedEntityName = $translator->trans("entity.$this->entityName", [], 'messages');
        $message = $translator->trans(
            'entity.modified',
            ['%entityName%' => $translatedEntityName],
            'messages'
        );

        return $this->entityFormHandling($request, $entity, $message);
    }

    /**
     * @param $slug
     *
     * @return RedirectResponse
     */
    public function delete($slug)
    {
        if (false === $this->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        $entity = $this->findBySlugOrThrowException($slug);

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->remove($entity);
        $entityManager->flush();

        $translator = $this->get('translator');
        $translatedEntityName = $translator->trans("entity.$this->entityName", [], 'messages');
        $message = $translator->trans(
            'entity.deleted',
            [
                '%entityName%' => $translatedEntityName,
                '%entity%' => $entity->getTitle(),
            ],
            'messages'
        );
        $this->addFlashMessage($message, 'success');

        return $this->redirectToRoute('MealzMealBundle_' . $this->entityName);
    }

    /**
     * @return JsonResponse
     */
    public function getEmptyForm()
    {
        if (false === $this->getUser()) {
            return $this->ajaxSessionExpiredRedirect();
        }

        if (false === $this->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        $entity = new $this->entityClassPath();
        $action = $this->generateUrl('MealzMealBundle_' . $this->entityName . '_new');

        return new JsonResponse($this->getRenderedEntityForm($entity, $action));
    }

    /**
     * @param $slug
     *
     * @return JsonResponse
     */
    public function getPreFilledForm($slug)
    {
        if (false === $this->getUser()) {
            return $this->ajaxSessionExpiredRedirect();
        }

        if (false === $this->isGranted('ROLE_KITCHEN_STAFF')) {
            throw new AccessDeniedException();
        }

        $entity = $this->repository->findOneBy(['slug' => $slug]);

        if (null === $entity) {
            return new JsonResponse(null, 404);
        }

        $action = $this->generateUrl('MealzMealBundle_' . $this->entityName . '_edit', ['slug' => $slug]);

        return new JsonResponse($this->getRenderedEntityForm($entity, $action, true));
    }

    /**
     * @param $entity
     * @param $action
     * @param bool $wrapInTr
     *
     * @return string
     */
    private function getRenderedEntityForm($entity, $action, $wrapInTr = false)
    {
        $form = $this->createForm(
            $this->getNewForm(),
            $entity,
            ['action' => $action]
        );

        $template = "MealzMealBundle:$this->entityName/partials:form.html.twig";
        if ($wrapInTr) {
            $template = "MealzMealBundle:$this->entityName/partials:formTable.html.twig";
        }

        $renderedForm = $this->render($template, ['form' => $form->createView()]);

        return $renderedForm->getContent();
    }

    /**
     * @param $entity
     * @param $successMessage
     *
     * @return RedirectResponse|Response
     */
    private function entityFormHandling(Request $request, $entity, $successMessage)
    {
        $form = $this->createForm($this->getNewForm(), $entity);

        // handle form submission
        if ($request->isMethod('POST')) {
            $form->handleRequest($request);

            if ($form->isValid()) {
                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($entity);
                $entityManager->flush();

                $this->addFlashMessage($successMessage, 'success');
            } else {
                return $this->renderEntityList([
                    'form' => $form->createView(),
                ]);
            }
        }

        return $this->redirectToRoute('MealzMealBundle_' . $this->entityName);
    }

    /**
     * @param array $parameters
     *
     * @return Response
     */
    protected function renderEntityList($parameters = [])
    {
        $entities = $this->getEntities();

        $defaultParameters = [
            'entities' => $entities,
        ];

        $mergedParameters = array_merge($defaultParameters, $parameters);

        return $this->render('MealzMealBundle:' . $this->entityName . ':list.html.twig', $mergedParameters);
    }

    /**
     * @return array
     */
    protected function getEntities()
    {
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
        $entity = $this->repository->findOneBy(['slug' => $slug]);
        if (null === $entity) {
            throw $this->createNotFoundException();
        }

        return $entity;
    }

    protected function getNewForm(): string
    {
        return $this->entityFormName;
    }
}
