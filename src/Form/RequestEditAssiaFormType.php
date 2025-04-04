<?php

namespace App\Form;

use App\Entity\Video;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class RequestEditAssiaFormType extends AbstractType
{

    private $allowedMimeTypes = [
        'mp4' => 'video/mp4',
        'mov' => 'video/quicktime',
    ];

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder
            ->add('notes', TextareaType::class, [
                'label' => false,
                'attr' => [
                    'placeholder' => 'Vos nouvelles instructions',
                    'rows' => '4',
                    'class' => 'w-50'
                ],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Veuillez renseigner vos instructions.',
                    ]),
                    new Length([
                        'min' => 3,
                        'minMessage' => 'Les instructions doit contenir au minimum {{ limit }} caractères !',
                        'max' => 20000,
                        'maxMessage' => 'Les instructions ne doit pas dépasser {{ limit }} caractères !',
                    ]),
                ],
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Envoyer',
                'attr' => [
                    'class' => 'link mx-auto btn-save d-flex justify-content-center',
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Video::class,
            'attr' => [
                'novalidate' => 'novalidate',
            ],
        ]);
    }
}