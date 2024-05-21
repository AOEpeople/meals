<?php

namespace App\Mealz\MealBundle\Controller;

use Doctrine\Persistence\ObjectRepository;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
     * @return array
     */
    protected function getEntities()
    {
        return $this->repository->findAll();
    }

    /**
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
}
