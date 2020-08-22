<?php

namespace App\Form;

use App\Entity\Article;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Form\Extension\Validator\Type\UploadValidatorExtension;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;


class ArticleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner un titre'
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Le titre doit contenir au moins {{ limit }} caractères',
                        'max' => 255,
                        'maxMessage' => 'Le titre doit contenir au maximum {{ limit }} caractères'
                    ]),
                ]
            ])
            ->add('description', CKEditorType::class, [
                'label' => 'Description',
                'purify_html' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner une description'
                    ]),
                    new Length([
                        'min' => 5,
                        'minMessage' => 'Le champ doit contenir au moins {{ limit }} caractères',
                        'max' => 10000,
                        'maxMessage' => 'Le champ doit contenir au maximum {{ limit }} caractères'
                    ]),
                ]
            ])
            ->add('details', CKEditorType::class, [
                'label' => 'Détails',
                'purify_html' => true,
                'constraints' => [
                    new Length([
                        'max' => 5000,
                        'maxMessage' => 'Le champ doit contenir au maximum {{ limit }} caractères'
                    ]),
                ]
            ])
            ->add('missions', CKEditorType::class, [
                'label' => 'Missions (bénévoles)',
                'purify_html' => true,
                'constraints' => [
                    new Length([
                        'max' => 5000,
                        'maxMessage' => 'Le champ doit contenir au maximum {{ limit }} caractères'
                    ]),
                ]
            ])
            ->add('mainPhoto', FileType::class, [
                'label' => 'Sélectionnez une image principale',
                'attr' => [
                    'accept' => 'image/jpeg, image/png'
                ],
                'constraints' => [
                    new File([
                        'maxSize' => '20M',
                        'mimeTypes' => [
                            'image/jpeg',
                            'image/png',
                            'image/jpg',
                        ],
                        'mimeTypesMessage' => 'L\'image doit être de type jpg ou png',
                        'maxSizeMessage' => 'Fichier trop volumineux : ({{ size }} {{ suffix }}). La taille maximum autorisée est {{ limit }} {{ suffix }}',
                    ]),
                ]
            ])
            ->add('gallery', FileType::class, [
                'label' => 'Sélectionnez une galerie d\'images',
                'multiple' => true,
                'mapped' => false,
            ])
            ->add('dateBeginning', DateTimeType::class, [
                'label' => 'Date et heure de début'
            ])
            ->add('dateEnd', DateTimeType::class, [
                'label' => 'Date et heure de fin'
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Ajouter un article'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Article::class,
        ]);
    }
}
