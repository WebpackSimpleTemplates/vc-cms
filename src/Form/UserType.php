<?php

namespace App\Form;

use App\Entity\User;
use App\Transformer\UploadFileTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function __construct(
        private UploadFileTransformer $uploadFileTransformer
    ) {}

    protected function _buildForm(FormBuilderInterface $builder, array $options) {
         $builder
            ->add('avatar', FileType::class, [
                "attr" => [
                    "accept" => "image/*",
                ],
                "label" => "Аватар",
                "required" => false,
            ])
            ->add('email', EmailType::class, [ "label" => "Электронная почта" ])
            ->add('fullname', TextType::class, [ "label" => "ФИО" ])
            ->add('displayName', TextType::class, [ "label" => "Отображаемое имя" ])
            ->add('roles', ChoiceType::class, [
                "choices" => [
                    "Администратор" => "ROLE_ADMIN",
                    "Читатель" => "ROLE_READER",
                    "Консультант" => "ROLE_OPERATOR",
                ],
                "label" => "Роли-доступы",
                'expanded' => true,
                'multiple' => true,
            ])
        ;

        $builder->get('avatar')->addViewTransformer($this->uploadFileTransformer);
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $this-> _buildForm($builder, $options);

        $builder
            ->add('plainPassword', PasswordType::class, [
                "required" => false,
                'mapped' => false,
                "label" => "Новый пароль",
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
