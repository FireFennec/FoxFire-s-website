<?php

declare(strict_types=1);

namespace App\Form\PersonalSkills;

use App\Entity\PersonalSkill;
use App\Model\PersonalSkillLevelEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Defines the form for creating and editing personal skills.
 *
 * @extends AbstractType<PersonalSkill>
 */
class PersonalSkillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('skillName', TextType::class, [
                'label' => 'Skill Name',
                'required' => true,
                'empty_data' => '',
            ])
            ->add('shortDescription', TextareaType::class, [
                'label' => 'Short Description',
                'required' => false,
                'attr' => [
                    'maxlength' => 300,
                ],
            ])
            ->add('skillDescription', TextareaType::class, [
                'label' => 'Long Description',
                'required' => true,
                'empty_data' => '',
                'attr' => [
                    'maxlength' => 2000,
                ],
            ])
            ->add('levelOfSkill', EnumType::class, [
                'class' => PersonalSkillLevelEnum::class,
                'choice_label' => static fn (PersonalSkillLevelEnum $level): string => $level->displayLabel(),
                'label' => 'Skill Level',
                'required' => true,
            ])
            ->add('submit', SubmitType::class, [
                'label' => $options['submit_label'],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PersonalSkill::class,
            'submit_label' => 'Create Skill',
        ]);
    }
}
