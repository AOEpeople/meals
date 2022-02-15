<?php

declare(strict_types=1);

namespace App\Mealz\MealBundle\Form\Guest;

use App\Mealz\MealBundle\Entity\Day;
use App\Mealz\MealBundle\Entity\InvitationWrapper;
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

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        /** @var Day $day */
        $day = $options['data']->getDay();

        $builder
            ->add('slot', ChoiceType::class, [
                'choices' => $this->slotRepo->findBy(['disabled' => 0, 'deleted' => 0], ['order' => 'ASC']),
                'choice_label' => 'title',
                'choice_value' => 'slug',
                /* @SuppressWarnings(PHPMD.UnusedFormalParameter) */
                'choice_attr' => static function (Slot $slot, string $key, string $slug) {
                    return [
                        'data-limit' => $slot->getLimit(),
                        'data-title' => $slot->getTitle(),
                    ];
                },
                'placeholder' => $this->translator->trans('content.participation.meal.select_slot', [], 'general'),
                'attr' => ['class' => 'slot-selector'],
                'required' => false,
                'disabled' => !$day->getMeals()->containsBookableMeal(),
            ])
            ->add('day', DayForm::class, [
                'data' => $day,
            ])
            ->add('profile', ProfileForm::class)
            ->add('save', SubmitType::class, [
                'label' => 'button.save',
                'translation_domain' => 'actions',
                'attr' => [
                    'class' => 'button small',
                    'data-qa' => 'submit',
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => InvitationWrapper::class,
            'csrf_protection' => false,
        ]);
    }
}
