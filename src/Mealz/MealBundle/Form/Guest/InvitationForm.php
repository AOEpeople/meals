<?php

namespace App\Mealz\MealBundle\Form\Guest;

use App\Mealz\MealBundle\Entity\Slot;
use App\Mealz\MealBundle\Entity\SlotRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class InvitationForm extends AbstractType
{
    private TranslatorInterface $translator;
    private SlotRepository $slotRepo;

    public function __construct(TranslatorInterface $translator, SlotRepository $slotRepo)
    {
        $this->translator = $translator;
        $this->slotRepo = $slotRepo;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        // passing by Day value to the DayForm in order to render only particular day
        $builder
            ->add('slot', ChoiceType::class, [
                'choices' => $this->slotRepo->findBy(['disabled' => 0, 'deleted' => 0]),
                'choice_label' => 'title',
                'choice_value' => 'slug',
                'choice_attr' => static function (Slot $slot, string $slug, string $title) {
                    return ['data-limit' => $slot->getLimit()];
                },
                'placeholder' => $this->translator->trans('content.participation.meal.select_slot', [], 'general'),
                'attr' => ['class' => 'slot-selector']
            ])
            ->add('day', DayForm::class, [
                'data' => $options['data']->getDay(),
            ])
            ->add('profile', ProfileForm::class)
            ->add('save', SubmitType::class, [
                'label' => 'button.save',
                'translation_domain' => 'actions',
                'attr' => [
                    'class' => 'button small',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => 'App\Mealz\MealBundle\Entity\InvitationWrapper',
            'csrf_protection' => false,
        ]);
    }
}
