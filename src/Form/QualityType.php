<?php

namespace App\Form;

use App\Entity\Quality;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QualityType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('title', TextType::class, [
                "label" => "Название"
            ])
            ->add('description', TextareaType::class, [
                "label" => "Описание",
                'required' => false
            ])
            ->add('isMain', CheckboxType::class, [
                "label" => "Общая для всех",
                "required" => false,
            ])
            ->add('isConsultant', CheckboxType::class, [
                "label" => "Влияет на рейтинг консультанта",
                "required" => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Quality::class,
        ]);
    }
}
