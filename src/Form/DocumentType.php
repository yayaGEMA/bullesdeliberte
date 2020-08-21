<?php

namespace App\Form;

use App\Entity\Document;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;


class DocumentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'Nom du fichier',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner un nom'
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Le nom doit contenir au moins {{ limit }} caractères',
                        'max' => 255,
                        'maxMessage' => 'Le titre doit contenir au maximum {{ limit }} caractères'
                    ]),
                ]
            ])
            ->add('file', FileType::class, [
                'label' => 'Sélectionnez un fichier (formats acceptés : .jpeg, .pdf)',
                'constraints' => [
                    new File([
                        'maxSize' => '2M',
                        'mimeTypes' => [
                            'application/pdf',
                            'image/jpeg'
                        ],
                        'mimeTypesMessage' => 'Seuls les formats jpeg et pdf sont acceptés',
                        'maxSizeMessage' => 'Fichier trop volumineux : ({{ size }} {{ suffix }}). La taille maximum autorisée est {{ limit }} {{ suffix }}',
                    ])
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Ajouter un document',
                'attr' => [
                    'class' => 'btn btn-primary col-10 offset-1 col-md-6 offset-md-3'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Document::class,
        ]);
    }
}
