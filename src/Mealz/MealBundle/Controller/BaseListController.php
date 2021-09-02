<?php

namespace App\Mealz\MealBundle\Controller;

use Doctrine\ORM\EntityRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

abstract class BaseListController extends BaseController
{
    /**
     * @var EntityRepository
     */
    protected $repository;

    protected string $entityFormName;

    private string $entityName;

    private string $entityClassPath;

    public function setEntityName(string $entityName): void
    {
        $this->entityName = $entityName;
        $this->entityClassPath = '\App\Mealz\MealBundle\Entity\\'.$entityName;
        $this->entityFormName = '\App\Mealz\MealBundle\Form\\'.$entityName.'\\'.$entityName.'Form';
    }

    public function setRepository(EntityRepository $repository): void
    {
        $this->repository = $repository;
    }

    /**
     * list Action
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
        if ($this->isGranted('ROLE_KITCHEN_STAFF') === false) {
            throw new AccessDeniedException();
        }

        $translator = $this->get('translator');
        $translatedEntityName = $translator->trans("entity.$this->entityName", [], 'messages');
        $message = $translator->trans(
            'entity.added',
            ['%entityName%' => $translatedEntityName],
            'messages'
        );

        return $this->entityFormHandling($request, new $this->entityClassPath, $message);
    }

    /**
     * edit Action
     * @param Request $request
     * @param $slug
     * @return RedirectResponse|Response
     */
    public function edit(Request $request, $slug)
    {
        if ($this->isGranted('ROLE_KITCHEN_STAFF') === false) {
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
     * delete Action
     * @param $slug
     * @return RedirectResponse
     */
    public function delete($slug)
    {
        if ($this->isGranted('ROLE_KITCHEN_STAFF') === false) {
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

        return $this->redirectToRoute('MealzMealBundle_'.$this->entityName);
    }

    /**
     * get Empty Form Action
     * @return JsonResponse
     */
    public function getEmptyForm()
    {
        if ($this->getUser() === false) {
            return $this->ajaxSessionExpiredRedirect();
        }

        if ($this->isGranted('ROLE_KITCHEN_STAFF') === false) {
            throw new AccessDeniedException();
        }

        $entity = new $this->entityClassPath();
        $action = $this->generateUrl('MealzMealBundle_'.$this->entityName.'_new');

        return new JsonResponse($this->getRenderedEntityForm($entity, $action));
    }

    /**
     * get Pre filled Form Action
     * @param $slug
     * @return JsonResponse
     */
    public function getPreFilledForm($slug)
    {
        if ($this->getUser() === false) {
            return $this->ajaxSessionExpiredRedirect();
        }

        if ($this->isGranted('ROLE_KITCHEN_STAFF') === false) {
            throw new AccessDeniedException();
        }

        $entity = $this->repository->findOneBy(array('slug' => $slug));

        if ($entity === null) {
            return new JsonResponse(null, 404);
        }

        $action = $this->generateUrl('MealzMealBundle_'.$this->entityName.'_edit', ['slug' => $slug]);

        return new JsonResponse($this->getRenderedEntityForm($entity, $action, true));
    }

    /**
     * Get rendered Entity Form
     * @param $entity
     * @param $action
     * @param bool $wrapInTr
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
     * Entity Form Handling
     * @param Request $request
     * @param $entity
     * @param $successMessage
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

        return $this->redirectToRoute('MealzMealBundle_'.$this->entityName);
    }

    /**
     * render Entity List
     * @param array $parameters
     * @return Response
     */
    protected function renderEntityList($parameters = array())
    {
        $entities = $this->getEntities();

        $defaultParameters = [
            'entities' => $entities,
        ];

        $mergedParameters = array_merge($defaultParameters, $parameters);

        return $this->render('MealzMealBundle:'.$this->entityName.':list.html.twig', $mergedParameters);
    }

    /**
     * get Entities
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
        $entity = $this->repository->findOneBy(array('slug' => $slug));
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
