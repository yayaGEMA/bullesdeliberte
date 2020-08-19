<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\BirthdayType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Regex;
use FOS\CKEditorBundle\Form\Type\CKEditorType;


class EditProfileFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder

            //Champ prénom
            ->add('firstname', TextType::class, [
                'label' => 'Prénom*',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner un prénom'
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Ce champ doit contenir au moins {{ limit }} caractères',
                        'max' => 50,
                        'maxMessage' => 'Ce champ doit contenir au maximum {{ limit }} caractères'
                    ]),
                ]
            ])

            // Champ nom
            ->add('name', TextType::class, [
                'label' => 'Nom*',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner un nom de famille'
                    ]),
                    new Length([
                        'min' => 2,
                        'minMessage' => 'Ce champ doit contenir au moins {{ limit }} caractères',
                        'max' => 50,
                        'maxMessage' => 'Ce champ doit contenir au maximum {{ limit }} caractères'
                    ]),
                ]
            ])

            // Champ numéro de téléphone
            ->add('phone', TextType::class, [
                'label' => 'Numéro de téléphone',
                'constraints' => [
                    new Length([
                        'max' => 20,
                    ]),
                    new Regex([
                        'pattern' => "/^(?:(?:\+|00)33[\s.'-]{0,3}(?:\(0\)[\s.'-]{0,3})?|0)[1-9](?:(?:[\s.'-]?\d{2}){4}|\d{2}(?:[\s'.-]?\d{3}){2})$/",
                        'message' => "Votre numéro de téléphone n'est pas valide."
                    ]),
                ]
            ])

            // Champ date de naissance
            ->add('birthdate', BirthdayType::class, [
                'label' => 'Date de naissance',
            ])

            // Bouton de validation
            ->add('save', SubmitType::class, [
                'label' => 'Enregistrer les modifications',
                'attr' => [
                    'class' => 'btn btn-primary col-10 offset-1 col-md-6 offset-md-3'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'attr' => [
                'novalidate' => 'novalidate'
            ]
        ]);
    }
}
