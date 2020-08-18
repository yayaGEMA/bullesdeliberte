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

class EditArticleType extends AbstractType
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
                        'max' => 5000,
                        'maxMessage' => 'Le champ doit contenir au maximum {{ limit }} caractères'
                    ]),
                ]
            ])
            // Champ "gallery" non lié à la BDD
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
                'label' => 'Modifier l\'article'
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
