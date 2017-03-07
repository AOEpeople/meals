<?php

namespace Mealz\MealBundle\Form\MealAdmin;

use Mealz\MealBundle\Entity\Day;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Mealz\MealBundle\Entity\Meal;
use Doctrine\ORM\UnitOfWork;
use Symfony\Component\Validator\Constraints\Valid;
use Doctrine\ORM\EntityManager;

/**
 * Class DayForm
 * @package Mealz\MealBundle\Form\MealAdmin
 */
class DayForm extends AbstractType
{
    protected $em;

    /**
     * DayForm constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->em = $entityManager;
    }

    /**
     * build the Form
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'meals',
                CollectionType::class,
                array(
                    'entry_type' => MealForm::class,
                    'allow_delete' => true,
                    'delete_empty' => true,
                    'constraints' => new Valid(),
                    'allow_add' => true,
                )
            )
            ->add(
                'lockParticipationDateTime',
                DateTimeType::class,
                array(
                    'required' => false,
                    'widget' => 'single_text',
                    'format' => 'YYYY-MM-dd HH:mm:ss',
                    'attr' => array('class' => 'hidden-form-field')
                )
            )
            ->add(
                'enabled',
                CheckboxType::class,
                array(
                    'required' => false,
                    'attr' => array('class' => 'js-switch'),
                )
            );

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) use ($builder) {
            /** @var Day $day */
            $day = $event->getData();

            $meals = $day->getMeals();

            foreach ($meals as $meal) {
                /** @var Meal $meal */
                if (null === $meal->getDish() &&
                    $this->em->getUnitOfWork()->getEntityState($meal) == UnitOfWork::STATE_NEW
                ) {
                    $meals->removeElement($meal);
                }
            }

            $event->setData($day);
        });

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($builder) {
            $day = $event->getData();

            if (false === $day->getWeek()->isEnabled()) {
                $form = $event->getForm();
                $config = $form->get('enabled')->getConfig();
                $options = $config->getOptions();

                $form->add(
                    'enabled',
                    $config->getType()->getName(),
                    array_replace(
                        $options,
                        [
                            'disabled' => true
                        ]
                    )
                );
            }
        });
    }

    /**
     * configure the options
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Mealz\MealBundle\Entity\Day',
        ));
    }
}
