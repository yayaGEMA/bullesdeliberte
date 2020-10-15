<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use \DateTime;
use App\Form\EditProfileFormType;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mime\Address;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use SymfonyCasts\Bundle\VerifyEmail\Exception\VerifyEmailExceptionInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Mailer\MailerInterface;
use App\Recaptcha\RecaptchaValidator;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;
use Symfony\Component\HttpFoundation\Session\Session;


class RegistrationController extends AbstractController
{
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/creer-un-compte", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, RecaptchaValidator $recaptcha): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {

            // Si le captcha n'est pas valide, on crée une nouvelle erreur dans le formulaire (ce qui affichera l'erreur)
            // $request->request->get('g-recaptcha-response')  -----> code envoyé par le captcha dont la méthode verify() a besoin
            // $request->server->get('REMOTE_ADDR') -----> Adresse IP de l'utilisateur dont la méthode verify() a besoin
            if(!$recaptcha->verify( $request->request->get('g-recaptcha-response'), $request->server->get('REMOTE_ADDR') )){

                // Ajout d'une nouvelle erreur manuellement dans le formulaire
                $form->addError(new FormError('Le Captcha doit être validé !'));
            }

            if($form->isValid()){

                // encode the plain password
                $user->setPassword(
                    $passwordEncoder->encodePassword(
                        $user,
                        $form->get('plainPassword')->getData()
                    )
                )
                    // Date actuelle
                    ->setRegistrationDate(new DateTime())
                ;

                $entityManager = $this->getDoctrine()->getManager();
                $entityManager->persist($user);
                $entityManager->flush();

                // generate a signed url and email it to the user
                $this->emailVerifier->sendEmailConfirmation('app_verify_email', $user,
                    (new TemplatedEmail())
                        ->from(new Address('ne-pas-repondre@lesbullesdeliberte.org', '"ne-pas-repondre@lesbullesdeliberte.org"'))
                        ->to($user->getEmail())
                        ->subject('Veuillez confirmer votre adresse mail')
                        ->htmlTemplate('registration/confirmation_email.html.twig')
                );
                // do anything else you need here, like send an email

                // ajouter un message flash de succès
                $this->addFlash('success', 'Compte créé avec succès ! Un email de confirmation a été envoyé.');

                return $this->redirectToRoute('app_login');
            }
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');

        // validate email confirmation link, sets User::isVerified=true and persists
        try {
            $this->emailVerifier->handleEmailConfirmation($request, $this->getUser());
        } catch (VerifyEmailExceptionInterface $exception) {
            $this->addFlash('verify_email_error', $exception->getReason());

            return $this->redirectToRoute('app_register');
        }

        // @TODO Change the redirect on success and handle or remove the flash message in your templates
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_register');
    }

    /**
     * Page permettant à l'user de modifier des informations de profil
     *
     * @Route("/modifier-profil/{user_id}/", name="profile-edit")
     * @Security("is_granted('ROLE_USER')")
     *
     * @param \App\Entity\User $user
     * @param \Doctrine\ORM\EntityManagerInterface $manager
     * @Entity("user", expr="repository.find(user_id)")
     */
    public function profileEdit(User $user, Request $request, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder)
    {
        // Création du formulaire de modification
        $form = $this->createForm(EditProfileFormType::class, $user);

        // Liaison des données de requêtes (POST) avec le formulaire
        $form->handleRequest($request);

        // Si le formulaire est envoyé et n'a pas d'erreur
        if($form->isSubmitted() && $form->isValid()){

            // Définition du nouveau mdp
            $newPassword = $form->get('plainPassword')->getData();

            if(!$newPassword == null){
                // On hash le nouveau mdp
                $hashOfNewPassword = $encoder->encodePassword($user, $newPassword);
                
                $user->setPassword( $hashOfNewPassword );
            }

            // Sauvegarde des changements via le manager des entités
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            // Message flash de type "success"
            $this->addFlash('success', 'Informations modifiées avec succès !');

            // Redirection vers la page de profil
            return $this->redirectToRoute('profil');
        }

        // Appel de la vue en lui envoyant le formulaire à afficher
        return $this->render('main/editProfile.html.twig', [
            'form' => $form->createView(),
            'user' => $user,
        ]);
    }

    /**
     * Page servant à supprimer son compte via son id passé dans l'url
     *
     * @Route("/supprimer-compte/{id}/", name="account-delete")
     * @Security("is_granted('ROLE_USER')")
     */
    public function accountDelete(User $user, Request $request){

        // Si le token CSRF passé dans l'url n'est pas le token valide, message d'erreur
        if(!$this->isCsrfTokenValid('account_delete_'. $user->getId(), $request->query->get('csrf_token'))){

            $this->addFlash('error', 'Token sécurité invalide, veuillez réessayer.');

        } else {

            $session = $this->get('session');
            $session = new Session();
            $session->invalidate();

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($user);
            $entityManager->flush();

            // Message flash de type "success" pour indiquer la réussite de la suppression
            $this->addFlash('success', 'Compte supprimé avec succès !');

        }

        // Redirection de l'utilisateur sur la liste des articles
        return $this->redirectToRoute('main');
    }

}
