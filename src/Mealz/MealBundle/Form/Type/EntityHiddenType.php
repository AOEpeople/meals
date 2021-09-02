<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Form\Type;

use App\Mealz\MealBundle\Form\DataTransformer\EntityToIdTransformer;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EntityHiddenType extends AbstractType
{
    protected EntityManagerInterface $objectManager;

    public function __construct(EntityManagerInterface $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $transformer = new $options['transformer_class']($this->objectManager, $options['class']);
        $builder->addModelTransformer($transformer);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['class'])
            ->setDefaults([
                'invalid_message' => 'The entity does not exist.',
                'transformer_class' => EntityToIdTransformer::class,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getParent(): string
    {
        return HiddenType::class;
    }
}
