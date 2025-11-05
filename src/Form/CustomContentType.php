<?php

namespace App\Form;

use App\Entity\CustomContent;
use App\Transformer\UploadFileTransformer;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomContentType extends AbstractType
{
    public function __construct(
        private UploadFileTransformer $uploadFileTransformer
    ) {}

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('theme', ChoiceType::class, [
                "choices" => [
                    "Светлая" => "light",
                    "Тёмная" => "dark",
                    "Системная" => "auto",
                ],
                "label" => "Тема",
                'expanded' => false,
                'multiple' => false,
            ])
            ->add('logo', FileType::class, [
                "attr" => [
                    "accept" => "image/*",
                ],
                "label" => "Логотип",
                "required" => false,
                'empty_data' => '',
            ])
            ->add('logoDark', FileType::class, [
                "attr" => [
                    "accept" => "image/*",
                ],
                "label" => "Логотип для тёмной темы",
                "required" => false,
                'empty_data' => '',
            ])
            ->add('marquee', TextareaType::class, [
                "label" => "Бегущая строка",
                "required" => false,
            ])
        ;

        $builder->get('logo')->addViewTransformer($this->uploadFileTransformer);
        $builder->get('logoDark')->addViewTransformer($this->uploadFileTransformer);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => CustomContent::class,
        ]);
    }
}
