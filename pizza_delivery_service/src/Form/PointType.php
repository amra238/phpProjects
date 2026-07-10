<?php

namespace App\Form;

use App\Entity\Point;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;

class PointType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('latitude', NumberType::class, [
                'label' => 'Широта',
                'scale' => 6,
                'constraints' => [
                    new NotBlank(message: 'Укажите широту'),
                    new Range(min: -90, max: 90, notInRangeMessage: 'Широта от -90 до 90'),
                ],
            ])
            ->add('longitude', NumberType::class, [  // <-- ПРОВЕРЬ: нет пробела!
                'label' => 'Долгота',
                'scale' => 6,
                'constraints' => [
                    new NotBlank(message: 'Укажите долготу'),
                    new Range(min: -180, max: 180, notInRangeMessage: 'Долгота от -180 до 180'),
                ],
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Point::class,
        ]);
    }
}
