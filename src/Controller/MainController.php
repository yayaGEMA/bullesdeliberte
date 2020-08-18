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
use Knp\Component\Pager\PaginatorInterface;
use App\Entity\Participation;
use App\Repository\ParticipationRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;


class MainController extends AbstractController
{
    /**
     * @Route("", name="main")
     */
    public function index()
    {
        // Récupération du repository des articles
        $articleRepo = $this->getDoctrine()->getRepository(Article::class);

        // On demande au repository de nous donner les articles les plus récents
        $indexArticles = $articleRepo->findFourLatest();

        return $this->render('main/index.html.twig', [
            'index_articles' => $indexArticles
        ]);
    }

    /**
     * Page de création d'un "article" ou "event"
     *
     * @Route("/ajouter/", name="new_article")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function createArticle(Request $request)
    {
        // Création d'un article vide
        $newArticle = new Article();

        // Création d'un nouveau formulaire basé sur "ArticleType", qui hydratera notre article "$article"
        $form = $this->createForm(ArticleType::class, $newArticle);

        // Remplissage du traitement du formulaire avec les données POST (sous forme d'objet $request)
        $form->handleRequest($request);

        // Si le formulaire a été envoyé
        if($form->isSubmitted() && $form->isValid()){

            // Extraction de l'objet de la photo envoyée dans le formulaire
            $picture = $form->get('mainPhoto')->getData();

            // Création d'un nouveau nom aléatoire pour la photo avec son extension (récupérée via la méthode guessExtension() )
            $newFileName = md5(time() . rand() . uniqid() ) . '.' . $picture->guessExtension();

            // Déplacement de la photo dans le dossier que l'on avait paramétré dans le fichier services.yaml, avec le nouveau nom qu'on lui a généré
            $picture->move(
                $this->getParameter('app.article.photo.directory'),     // Emplacement de sauvegarde du fichier
                $newFileName    // Nouveau nom du fichier
            );

            $newArticle->setMainPhoto($newFileName);

            // Hydratation de l'article
            $newArticle
                ->setPublicationDate( new DateTime() )
                ->setParticipations(0)
            ;

            // Récupération du manager général des entités
            $entityManager = $this->getDoctrine()->getManager();

            // Persistance de l'article auprès de Doctrine
            $entityManager->persist($newArticle);

            // Sauvegarder en bdd
            $entityManager->flush();

            // ajouter un message flash de succès
            $this->addFlash('success', 'Article publié avec succès !');

            // Redirige sur la page des articles
            return $this->redirectToRoute('main');

        }

        return $this->render('main/createArticle.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * Page des événements à venir
     *
     * @Route("/evenements-futurs/", name="future_list")
     */
    public function futureList(Request $request, PaginatorInterface $paginator)
    {

        // On récupère dans l'url la données GET page (si elle n'existe pas, la valeur retournée par défaut sera la page 1)
        $requestedPage = $request->query->getInt('page', 1);

        // Si le numéro de page demandé dans l'url est inférieur à 1, erreur 404
        if($requestedPage < 1){
            throw new NotFoundHttpException();
        }

        // Récupération du manager des entités
        $em = $this->getDoctrine()->getManager();

        $now = date('Y-m-d H:i:s');

        // Création d'une requête qui servira au paginator pour récupérer les articles de la page courante
        $query = $em->createQuery('SELECT a FROM App\Entity\Article a WHERE a.dateBeginning > :now ORDER BY a.dateBeginning')->setParameter('now', $now);


        // On stocke dans $pageArticles les 10 articles de la page demandée dans l'URL
        $pageArticles = $paginator->paginate(
            $query,     // Requête de selection des articles en BDD
            $requestedPage,     // Numéro de la page dont on veux les articles
            5      // Nombre d'articles par page
        );

        // On envoi les articles récupérés à la vue
        return $this->render('main/futureList.html.twig', [
            'articles' => $pageArticles
        ]);

    }

    /**
     * Page des événements passés
     *
     * @Route("/evenements-passes/", name="past_list")
     */
    public function pastList(Request $request, PaginatorInterface $paginator)
    {

        // On récupère dans l'url la données GET page (si elle n'existe pas, la valeur retournée par défaut sera la page 1)
        $requestedPage = $request->query->getInt('page', 1);

        // Si le numéro de page demandé dans l'url est inférieur à 1, erreur 404
        if($requestedPage < 1){
            throw new NotFoundHttpException();
        }

        // Récupération du manager des entités
        $em = $this->getDoctrine()->getManager();

        $now = date('Y-m-d H:i:s');

        // Création d'une requête qui servira au paginator pour récupérer les articles de la page courante
        $query = $em->createQuery('SELECT a FROM App\Entity\Article a WHERE a.dateEnd< :now ORDER BY a.dateEnd DESC')->setParameter('now', $now);


        // On stocke dans $pageArticles les 10 articles de la page demandée dans l'URL
        $pageArticles = $paginator->paginate(
            $query,     // Requête de selection des articles en BDD
            $requestedPage,     // Numéro de la page dont on veux les articles
            5      // Nombre d'articles par page
        );

        // On envoi les articles récupérés à la vue
        return $this->render('main/pastList.html.twig', [
            'articles' => $pageArticles
        ]);

    }

    /**
     * Page d'affichage d'un événement en détail
     *
     * @Route("/evenement/{slug}/", name="event")
     */
    public function eventView(Article $article, Request $request)
    {
        $now = date('Y-m-d H:i:s');

        return $this->render('main/oneEvent.html.twig', [
            'event' => $article,
            'now' => $now
        ]);
    }

    /**
     * Page user permettant de modifier un event existant via son slug passé dans l'url
     *
     * @Route("/{slug}/modifier/", name="edit")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function articleEdit(Article $article, request $request)
    {

        // Création du formulaire de modification (c'est le même que le formulaire permettant de créer un nouveau article, sauf qu'il sera déjà rempli avec les données de "$article")
        $form = $this->createForm(EditArticleType::class, $article);

        // Liaison des données de requête (POST) avec le formulaire
        $form->handleRequest($request);

        // Si le formulaire est envoyé et n'a pas d'erreur
        if($form->isSubmitted() && $form->isValid()){

            // Sauvegarde des changements faits via le manager général des entités
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->flush();

            // Message flash de type "success"
            $this->addFlash('success', 'Article modifié avec succès !');

            // Redirection vers la page du bien modifié
            return $this->redirectToRoute('event', ['slug' => $article->getSlug()]);

        }

        // Appel de la vue en lui envoyant le formulaire à afficher
        return $this->render('main/editArticle.html.twig', [
            'form' => $form->createView(),
        ]);

    }

    /**
     * Page admin servant à supprimer un article via son id passé dans l'url
     *
     * @Route("/article/suppression/{id}/", name="delete")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function articleDelete(Article $article, Request $request){

        // Si le token CSRF passé dans l'url n'est pas le token valide, message d'erreur
        if(!$this->isCsrfTokenValid('delete_'. $article->getId(), $request->query->get('csrf_token'))){

            $this->addFlash('error', 'Token sécurité invalide, veuillez réessayer.');

        } else {

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($article);
            $entityManager->flush();

            // Message flash de type "success" pour indiquer la réussite de la suppression
            $this->addFlash('success', 'Article supprimé avec succès !');

        }

        // Redirection de l'utilisateur sur la liste des articles
        return $this->redirectToRoute('main');
    }

    /**
     * Page de don
     *
     * @Route("/faire-un-don/", name="donation")
     */
    public function donation(){

        return $this->render('main/donation.html.twig');
    }

    /**
     * Page de profil
     *
     * @Route("/mon-profil/", name="profil")
     * @Security("is_granted('ROLE_USER')")
     */
    public function profil(Request $request)
    {
        return $this->render('main/profil.html.twig');
    }

    /**
     * Page de présentation de l'association
     *
     * @Route("/presentation/", name="presentation")
     */
    public function presentation()
    {
        return $this->render('main/presentation.html.twig');
    }

    /**
     * Page de présentation des membres de l'association
     *
     * @Route("/qui-sommes-nous/", name="qui-sommes-nous")
     */
    public function quiSommesNous()
    {
        return $this->render('main/quiSommesNous.html.twig');
    }

    /**
     * Page d'articles de presse
     *
     * @Route("/coin-presse/", name="coin-presse")
     */
    public function coinPresse()
    {
        return $this->render('main/coinPresse.html.twig');
    }

    /**
     * Page de documentation
     *
     * @Route("/documentation/", name="documentation")
     */
    public function documentation()
    {
        return $this->render('main/documentation.html.twig');
    }

    /**
     * Permet à un user d'ajouter ou d'enlever sa participation
     *
     * @Route("/evenement/{id}/participation", name="event_participation")
     *
     * @param \App\Entity\Article $article
     * @param \Doctrine\ORM\EntityManagerInterface $manager
     * @param \App\Repository\ParticipationRepository $participationRepo
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function participation(Article $article, EntityManagerInterface $manager, ParticipationRepository $participationRepo) : Response
    {
        // On récupère l'user
        $user = $this->getUser();

        // S'il n'est pas connecté
        if(!$user){
            // Tableau JSON avec le code d'erreur et le message
            return $this->json([
                'code' => 403,
                'message' => 'Il faut être connecté'
            ], 403);
        } else {

            // Si l'user participe déjà, on retire sa participation
            if($article->willCome($user)){
                $participation = $participationRepo->findOneBy([
                    'article' => $article,
                    'user' => $user
                ]);

                // On baisse le nb de participations
                $participations = $article->getParticipationsCounter();
                $article->setParticipationsCounter(--$participations);

                // On enlève sa participation dans la BDD
                $manager->remove($participation);
                $manager->persist($article);
                $manager->flush();

                // Tableau JSON avec le code de succès et le message
                return $this->json([
                    'code' => 200,
                    'message' => "Participation supprimée",
                    'participations' => $article->getParticipationsCounter()
                ], 200);

            // Sinon, il ajoute sa participation
            } else {

                // On spécifie quel user participe
                $participation = new Participation();
                $participation
                    ->setArticle($article)
                    ->setUser($user)
                ;

                // On augmente le nb de participations
                $participations = $article->getParticipationsCounter();
                $article->setParticipationsCounter(++$participations);

                // On ajoute sa participation dans la BDD
                $manager->persist($participation);
                $manager->persist($article);
                $manager->flush();

                // Tableau JSON avec le code de succès et le message
                return $this->json([
                    'code' => 200,
                    'message' => 'Participation ajoutée',
                    'participations' => $article->getParticipationsCounter()
                ], 200);
            }
        }
    }
}
