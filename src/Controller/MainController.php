<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use App\Entity\Article;
use App\Repository\ArticleRepository;
use App\Form\ArticleType;
use Symfony\Component\HttpFoundation\Request;
use \DateTime;
use Knp\Component\Pager\PaginatorInterface;


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
     * @Route("/articles/", name="future_list")
     */
    public function articleList(Request $request, PaginatorInterface $paginator)
    {

        // On récupère dans l'url la données GET page (si elle n'existe pas, la valeur retournée par défaut sera la page 1)
        $requestedPage = $request->query->getInt('page', 1);

        // Si le numéro de page demandé dans l'url est inférieur à 1, erreur 404
        if($requestedPage < 1){
            throw new NotFoundHttpException();
        }

        // Récupération du manager des entités
        $em = $this->getDoctrine()->getManager();

        // Création d'une requête qui servira au paginator pour récupérer les articles de la page courante
        $query = $em->createQuery('SELECT a FROM App\Entity\Article a');

        // On stocke dans $pageArticles les 10 articles de la page demandée dans l'URL
        $pageArticles = $paginator->paginate(
            $query,     // Requête de selection des articles en BDD
            $requestedPage,     // Numéro de la page dont on veux les articles
            5      // Nombre d'articles par page
        );

        // On envoi les articles récupérés à la vue
        return $this->render('main/articleList.html.twig', [
            'articles' => $pageArticles
        ]);

    }
}
