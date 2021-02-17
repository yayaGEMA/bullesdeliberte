<?php

namespace App\Form;

use App\Entity\Contact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class ContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('objet', TextType::class, [
                'label' => 'Objet de ma demande',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner un objet'
                    ]),
                    new Length([
                        'max' => 200,
                        'maxMessage' => 'Le champ doit contenir au maximum {{ limit }} caractères'
                    ])
                ]
            ])
            ->add('name', TextType::class, [
                'label' => 'NOM Prénom',
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner un nom et un prénom'
                    ]),
                ]
            ])
            ->add('email', EmailType::class,[
                'label' => 'Adresse Email',
                'constraints' => [
                    new Email([
                        'message' => 'L\'adresse email {{ value }} n\'est pas une adresse valide'
                    ]),
                    new NotBlank([
                        'message' => 'Merci de renseigner une adresse email'
                    ])
                ],
            ])
            ->add('content', TextareaType::class, [
                'label' => 'Ma demande',
                'attr' => ['rows' => '5'],
                'constraints' => [
                    new NotBlank([
                        'message' => 'Merci de renseigner une demande (au moins 20 caractères)'
                    ]),
                    new Length([
                        'min' => 20,
                        'minMessage' => 'Le champ doit contenir au moins {{ limit }} caractères',
                        'max' => 10000,
                        'maxMessage' => 'Le champ doit contenir au maximum {{ limit }} caractères'
                    ]),
                ]
            ])
            ->add('save', SubmitType::class, [
                'label' => 'ENVOYER',
                'attr' => [
                    'class' => 'btn col-12 col-md-6 offset-md-3'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Contact::class
        ]);
    }
}
