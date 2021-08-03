<?php
namespace Mealz\MealBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Mealz\MealBundle\Form\DataTransformer\EntityToIdTransformer;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class EntityHiddenType
 * @package Mealz\MealBundle\Form\Type
 */
class EntityHiddenType extends AbstractType
{
    /**
     * @var ObjectManager
     */
    protected $objectManager;

    /**
     * EntityHiddenType constructor.
     * @param ObjectManager $objectManager
     */
    public function __construct(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;
    }

    /**
     * build the Form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $transformer = new $options['transformer_class']($this->objectManager, $options['class']);
        $builder->addModelTransformer($transformer);
    }

    /**
     * set default Options
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setRequired(array('class'))
            ->setDefaults(
                array(
                    'invalid_message' => 'The entity does not exist.',
                    'transformer_class' => 'Mealz\MealBundle\Form\DataTransformer\EntityToIdTransformer',
                )
            );
    }

    /**
     * get the Parent
     * @return string
     */
    public function getParent()
    {
        return \Symfony\Component\Form\Extension\Core\Type\HiddenType::class;
    }
}
