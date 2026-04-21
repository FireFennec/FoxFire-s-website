<?php

namespace App\Form\Features;

use App\Entity\WebsiteFeature;
use App\Model\FeaturePriorityEnum;
use App\Model\FeaturesStatusEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateFeatureForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class)
            ->add('description', TextAreaType::class)
            ->add('status', EnumType::class, [
                'class' => FeaturesStatusEnum::class,
                'choice_label' => fn (FeaturesStatusEnum $choice) => $choice->value,
            ])
            ->add('priority', EnumType::class, [
                'class' => FeaturePriorityEnum::class,
                'choice_label' => fn (FeaturePriorityEnum $choice) => $choice->value,
            ])
            ->add('deadlineAt', DateType::class, [
                'input' => 'datetime_immutable',
                'widget' => 'single_text',
                'required' => false,
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['submit_label'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => WebsiteFeature::class,
            'submit_label' => 'Create Feature',
        ]);
    }
}
