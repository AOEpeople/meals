<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Form\MealAdmin;

use App\Mealz\MealBundle\Entity\Event;
use App\Mealz\MealBundle\Form\Type\EntityHiddenType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class EventForm extends AbstractType
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // we take day value from parent day form; see configureOptions() method below.
        $builder
            ->add('event', EntityHiddenType::class, [
                'class' => Event::class,
            ]);

        $builder->addEventListener(FormEvents::PRE_SET_DATA, static function (FormEvent $formEvent) {
            /** @var Meal|null $meal */
            $event = $formEvent->getData();
            if (null === $event) {
                return;
            }
            $day = $meal->getDay();
            $week = $day->getWeek();
            if (false === $day->isEnabled() || false === $week->isEnabled()) {
                $form = $event->getForm();
                $config = $form->get('event')->getConfig();
                $opts = $config->getOptions();
                $opts['attr'] = ['readonly' => 'readonly'];
                $form->add('event', EntityHiddenType::class, $opts);
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            /** @var array $data client submitted event data */
            $data = $event->getData();

            if (isset($data['dish']) && '' === $data['dish']) {
                // Empty dish value from client means meal needs to be deleted.
                // We cannot set meal's dish to null, so we just mark the meal for removal.
                /** @var Meal $meal */
                $meal = $event->getForm()->getData();
                $data['dish'] = $meal->getDish()->getId();  // restore original dish value
                $this->entityManager->remove($meal);        // mark the meal to be removed
                $meal->getDay()->removeMeal($meal);

                $event->setData($data);
                $event->getForm()->setData($meal);
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, static function (FormEvent $event) {
            /** @var Event $event */
            $event = $event->getData();
            $dishPrice = $meal->getDish()->getPrice();
            $meal->setPrice($dishPrice);
            $event->setData($meal);
        });
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Event::class,
            'empty_data' => static function (FormInterface $form) {
                $event = $form->get('event')->getData();
                $day = $form->getParent()->getParent()->getData();
                return $event;
                #return new Event($event, $day);
            },
            'error_bubbling' => false,
        ]);
    }
}
