<?php

namespace Mealz\TemplateBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ReviewController extends Controller
{
    public function indexAction()
    {
        return $this->render('MealzTemplateBundle:Review:index.html.twig', array(
            'form1' => $this->createForm1()->createView()
        ));
    }

    /**
     * Creates a Demo form for testAction
     * @return \Symfony\Component\Form\Form
     */
    private function createForm1()
    {
        $form = $this->createFormBuilder(null)

            ->add('textName', TextType::class, array(
                'label' => 'Input',
                'required' => false,
            ))
            ->add('textareaName', TextareaType::class, array(
                'label' => 'TextArea',
                'required' => false,
            ))
            ->add('date', \Symfony\Component\Form\Extension\Core\Type\DateType::class, array(
                'widget' => 'single_text',
                'label' => 'Date Select',
                'required' => false,
                'placeholder' => 'Placeholder',
            ))
            ->add('myRadioSingle', ChoiceType::class, array(
                'label' => 'Single Radio',
                'choices' => array('m' => 'Male'),
                'choices_as_values' => false,
                'required' => true,
                'multiple' => false,
                'expanded' => true,
            ))
            ->add('myRadio', ChoiceType::class, array(
                'label' => 'Radio List',
                'choices' => array('m' => 'Male', 'f' => 'Female', 't' => 'Transsexual', 'o' => 'Other'),
                'choices_as_values' => false,
                'multiple' => false,
                'expanded' => true,
            ))
            ->add('mySelect', ChoiceType::class, array(
                'label' => 'Select List',
                'choices' => array('m' => 'Male', 'f' => 'Female', 't' => 'Transsexual', 'o' => 'Other'),
                'choices_as_values' => false,
                'multiple' => false,
                'expanded' => false,
            ))
            ->add('mySelectEmpty', ChoiceType::class, array(
                'label' => 'Select List with empty',
                'choices' => array('m' => 'Male', 'f' => 'Female', 't' => 'Transsexual', 'o' => 'Other'),
                'choices_as_values' => false,
                'multiple' => false,
                'expanded' => false,
                'empty_value' => 'Choose your gender',
                'empty_data' => null,
            ))
            ->add('myCheckboxSingle', ChoiceType::class, array(
                'label' => 'Single Checkbox',
                'choices' => array('1' => 'Box 1'),
                'choices_as_values' => false,
                'multiple' => true,
                'expanded' => true,
            ))
            ->add('myCheckbox', ChoiceType::class, array(
                'label' => 'Checkbox List',
                'choices' => array(
                    '1' => 'Box 1',
                    '2' => 'Checkbox 2',
                    '3' => 'Checkbox 3',
                    '4' => 'This is a description with a super length. If you\'ve got much to tell to a specific field, it could be that the Description is as long as this one - so it will break apart in multilines!',
                    '5' => 'This is a description with a super length. If you\'ve got much to tell to a specific field, it could be that the Description is as long as this one - so it will break apart in multilines!',
                    '6' => 'One more'
                ),
                'choices_as_values' => false,
                'multiple' => true,
                'expanded' => true,
            ))
            ->add('save', SubmitType::class, array('label' => 'Save'))
            ->getForm();

        return $form;
    }
}
