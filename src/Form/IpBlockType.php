<?php

namespace App\Form;

use App\Entity\IpBlock;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class IpBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('ip', TextType::class, ['label' => "IP адрес"])
            ->add('publicReason', TextareaType::class, ['label' => 'Отображаемая причина'])
            ->add('privateReason', TextareaType::class, ['label' => 'Закрытая причина'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => IpBlock::class,
        ]);
    }
}
