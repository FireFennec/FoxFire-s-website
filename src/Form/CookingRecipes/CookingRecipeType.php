<?php

declare(strict_types=1);

namespace App\Form\CookingRecipes;

use App\Entity\CookingRecipe;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CookingRecipeType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('recipeName', TextType::class, [
                'required' => true,
            ])
            ->add('ingredients', CollectionType::class, [
                'entry_type' => TextType::class,
                'entry_options' => [
                    'required' => false,
                ],
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'prototype' => true,
            ])
            ->add('cookingProcess', TextareaType::class, [
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['submit_label'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CookingRecipe::class,
            'submit_label' => 'Create Recipe',
        ]);
    }
}
