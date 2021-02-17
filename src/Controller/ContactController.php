<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Mailer\MailerInterface;
use App\Entity\Contact;
use App\Form\ContactType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\Mime\Address;
use Symfony\Component\HttpFoundation\Request;
use App\Recaptcha\RecaptchaValidator;
use Symfony\Component\Form\FormError;


class ContactController extends AbstractController
{
    /**
     * Page contact
     * @Route("/contact/", name="contact")
     */
    public function contact(MailerInterface $mailer, Request $request, RecaptchaValidator $recaptcha)
    {
        $contact = new Contact();

        $form = $this->createForm(ContactType::class, $contact);

        $form->handleRequest($request);

        if ($form->isSubmitted()){

            if(!$recaptcha->verify( $request->request->get('g-recaptcha-response'), $request->server->get('REMOTE_ADDR') )){

                // Ajout d'une nouvelle erreur manuellement dans le formulaire
                $form->addError(new FormError('Le Captcha doit être validé !'));
            }

            if($form->isValid()){

                // Création du mail
                $email = (new TemplatedEmail())
                ->from(new Address($form->get('email')->getData()))
                ->to('marceaugerard1610@gmail.com')
                ->subject($form->get('objet')->getData())
                ->text($form->get('content')->getData())
                ->html('<p>'. $form->get('content')->getData() .'</p>')
                ;

                // Envoi du mail
                $mailer->send($email);

                $this->addFlash('success', 'Message envoyé ! Vous tâchons de vous répondre dans les plus brefs délais.');

                return $this->redirectToRoute('main');
            }
        }

        return $this->render('main/contact.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
