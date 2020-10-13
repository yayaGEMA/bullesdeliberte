<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Form\ArticleType;
use App\Form\EditArticleType;
use Symfony\Component\HttpFoundation\Request;
use \DateTime;
use App\Entity\User;
use App\Entity\Gallery;
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\Participation;
use App\Repository\ParticipationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Entity\Document;
use App\Form\DocumentType;
use App\Entity\PageText;
use App\Form\PageTextType;
use App\Entity\Reunion;
use App\Entity\ReunionRepository;
use App\Form\ReunionType;
use App\Form\EditReunionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Entity;

class ReunionController extends AbstractController
{
    /**
     * Page des réunions
     *
     * @Route("/reunions/", name="reunions")
     * @Security("is_granted('ROLE_USER')")
     */
    public function reunions(Request $request)
    {
        // Création d'une réunion vide
        $newReunion = new Reunion();

        // Formulaire
        $form = $this->createForm(ReunionType::class, $newReunion);

        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        // Si le formulaire est OK et envoyé
        if($form->isSubmitted() && $form->isValid()){

            $em->persist($newReunion);
            $em->flush();
            $this->addFlash('success', 'Réunion ajoutée avec succès !');
            return $this->redirectToRoute('reunions');
        }

        // On récupère le repo des réunions
        $reuRepo = $this->getDoctrine()->getRepository(Reunion::class);

        $reunions = $reuRepo->findAll();

        return $this->render('main/reunions.html.twig', [
            'reunions' => $reunions,
            'form' => $form->createView()
        ]);
    }

    /**
     * Page de modification d'une réunion via l'id
     *
     * @Route("reunion/modifier/{id}", name="edit_reunion")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function reunionEdit(Reunion $reunion, Request $request){

        // Création du form de modif
        $form = $this->createForm(EditReunionType::class, $reunion);

        // Liaison des données de requête (POST) avec le form
        $form->handleRequest($request);

        // Si le form est envoyé et ok
        if($form->isSubmitted() && $form->isValid()){

            $em = $this->getDoctrine()->getManager();
            $em->persist($reunion);
            $em->flush();

            // Message flash de type "success"
            $this->addFlash('success', 'Reunion modifiée avec succès !');

            // Redirection
            return $this->redirectToRoute('reunions');
        }

        // Appel de la vue
        return $this->render('main/editReunion.html.twig', [
            'form' => $form->createView(),
            'reunion' => $reunion,
        ]);
    }

    /**
     * Page de suppression de réunion
     *
     * @Route("/reunion/suppression/{id}/", name="delete_reunion")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function reunionDelete(Reunion $reunion, Request $request){

        // Si le token n'est pas le bon
        if(!$this->isCsrfTokenValid('delete_reunion_'. $reunion->getId(), $request->query->get('csrf_token'))){
            $this->addFlash('error', 'Token sécurité invalide, veuillez réessayer.');
        } else {

            $em = $this->getDoctrine()->getManager();
            $em->remove($reunion);
            $em->flush();

            // Message de succès
            $this->addFlash('success', 'Réunion supprimée avec succès !');
        }

        // Redirection
        return $this->redirectToRoute('reunions');
    }

}
