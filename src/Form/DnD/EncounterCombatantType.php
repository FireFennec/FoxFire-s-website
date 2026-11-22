<?php

declare(strict_types=1);

namespace App\Form\DnD;

use App\Entity\EncounterCombatant;
use App\Model\EncounterSideEnum;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EnumType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @extends AbstractType<EncounterCombatant>
 */
class EncounterCombatantType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Name',
            ])
            ->add('side', EnumType::class, [
                'class' => EncounterSideEnum::class,
                'label' => 'Side',
                'choice_label' => static fn (EncounterSideEnum $side): string => match ($side) {
                    EncounterSideEnum::ALLY => 'Ally',
                    EncounterSideEnum::ENEMY => 'Enemy',
                },
            ])
            ->add('maxHp', IntegerType::class, [
                'label' => 'Maximum HP',
            ])
            ->add('initiative', IntegerType::class, [
                'label' => 'Initiative',
            ]);

        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event): void {
            $combatant = $event->getData();

            if (!$combatant instanceof EncounterCombatant) {
                return;
            }

            $maxHp = $combatant->getMaxHp();
            $currentHp = $combatant->getCurrentHp();

            if ($maxHp !== null && $maxHp > 0 && $currentHp !== null && $currentHp > $maxHp) {
                $combatant->setCurrentHp($maxHp);
            }
        }, 1024);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EncounterCombatant::class,
        ]);
    }
}
