<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CreateUserType extends UserType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this-> _buildForm($builder, $options);
        $builder
            ->add('plainPassword', PasswordType::class, [
                "required" => true,
                'mapped' => false,
                "label" => "Пароль",
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
