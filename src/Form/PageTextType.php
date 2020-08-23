<?php

namespace App\Form;

use App\Entity\PageText;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use FOS\CKEditorBundle\Form\Type\CKEditorType;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;


class PageTextType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('content', CKEditorType::class, [
                'label' => 'Contenu de la page',
                'purify_html' => true,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de ne pas laisser ce champ vide'
                    ]),
                ]
            ])
            // Bouton de validation
            ->add('save', SubmitType::class, [
                'label' => 'Publier',
                'attr' => [
                    'class' => 'btn btn-primary col-10 offset-1 col-md-6 offset-md-3'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PageText::class,
        ]);
    }
}
