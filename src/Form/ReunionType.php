<?php

namespace App\Form;

use App\Entity\Reunion;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;


class ReunionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', TextType::class, [
                'label' => 'Titre (facultatif)',
                'required' => false,
                'constraints' => [
                    new Length([
                        'max' => 255,
                        'maxMessage' => 'Le titre doit contenir au maximum {{ limit }} caractères'
                    ]),
                ]
            ])
            ->add('content', CKEditorType::class, [
                'label' => 'Description (facultatif)',
                'required' => false,
                'purify_html' => true,
                'constraints' => [
                    new Length([
                        'max' => 10000,
                        'maxMessage' => 'Le champ doit contenir au maximum {{ limit }} caractères'
                    ]),
                ]
            ])
            ->add('datetime', DateTimeType::class, [
                'label' => 'Date et heure',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner une date'
                    ]),
                ]
            ])
            ->add('place', TextType::class, [
                'label' => 'Adresse',
                'attr' => ['autocomplete' => 'disabled', 'class' => 'w-100']
            ])
            ->add('save', SubmitType::class, [
                'label' => 'Ajouter une réunion',
                'attr' => [
                    'class' => 'btn btn-primary col-10 offset-1 col-md-6 offset-md-3'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Reunion::class,
        ]);
    }
}
