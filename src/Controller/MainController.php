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

            // On récupère les photos de la gallerie transmises
            $gallery = $form->get('gallery')->getData();

            // Récupération du manager général des entités
            $entityManager = $this->getDoctrine()->getManager();

            // On boucle sur les photos
            foreach($gallery as $galleryImage){

                // Création d'un nouveau nom aléatoire pour les photos avec leur extension)
                $newGalleryFileName = md5(time() . rand() . uniqid() ) . '.' . $galleryImage->guessExtension();

                $galleryImage->move(
                    $this->getParameter('app.article.photo.directory'),
                    $newGalleryFileName
                );

                // On stocke le nom de la photo dans la BDD
                $img = new Gallery();
                $img->setName($newGalleryFileName);
                $newArticle->addGallery($img);

                $entityManager->persist($img);
            }

            $participation = new Participation();

            // Hydratation de l'article
            $newArticle
                ->setPublicationDate( new DateTime() )
                ->addParticipation($participation )
                ->setParticipationsCounter(0)
            ;

            // Persistance de l'article auprès de Doctrine
            $entityManager->persist($newArticle);
            $entityManager->persist($participation);


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

            // On récupère les photos de la gallerie transmises
            $gallery = $form->get('gallery')->getData();

            // Sauvegarde des changements faits via le manager général des entités
            $entityManager = $this->getDoctrine()->getManager();

            // On boucle sur les photos
            foreach($gallery as $galleryImage){

                $newGalleryFileName = md5(time() . rand() . uniqid() ) . '.' . $galleryImage->guessExtension();

                $galleryImage->move(
                    $this->getParameter('app.article.photo.directory'),
                    $newGalleryFileName
                );

                // On stocke le nom de la photo dans la BDD
                $img = new Gallery();
                $img->setName($newGalleryFileName);
                $article->addGallery($img);

                $entityManager->persist($img);
            }

            $entityManager->flush();

            // Message flash de type "success"
            $this->addFlash('success', 'Article modifié avec succès !');

            // Redirection vers la page du bien modifié
            return $this->redirectToRoute('event', ['slug' => $article->getSlug()]);

        }

        // Appel de la vue en lui envoyant le formulaire à afficher
        return $this->render('main/editArticle.html.twig', [
            'form' => $form->createView(),
            'article' => $article,
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
    public function presentation(Request $request)
    {
        // On récupère le repo des pageText
        $pageTextRepo = $this->getDoctrine()->getRepository(PageText::class);

        $textToDisplay = $pageTextRepo->findOneBy(['page' => 'presentation'], [ 'id' => 'DESC']);

        // Création d'un texte vide
        $newPageText = new PageText();

        // Nouveau formulaire
        $form = $this->createForm(PageTextType::class, $newPageText);

        // Remplissage du traitement du form
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $newPageText->setPage('presentation')
            ->setContent($form->get('content')->getData());

            // On supprime les éventuels anciens contenus ayant le même nom de page
            $textsToDelete = $pageTextRepo->findBy(['page' => 'presentation']);

            // Récupération de l'entitymanager
            $em = $this->getDoctrine()->getManager();
            $em->persist($newPageText);
            foreach($textsToDelete as $textToDelete){
                $em->remove($textToDelete);
            }
            $em->flush();

            // Message de succès
            $this->addFlash('success', 'Contenu de la page mis à jour !');

            // Redirection
            return $this->redirectToRoute('presentation');
        }

        return $this->render('main/presentation.html.twig', [
            'form' => $form->createView(),
            'text' => $textToDisplay
        ]);
    }

    /**
     * Page de présentation des membres de l'association
     *
     * @Route("/qui-sommes-nous/", name="qui-sommes-nous")
     */
    public function quiSommesNous(Request $request)
    {
        // On récupère le repo des pageText
        $pageTextRepo = $this->getDoctrine()->getRepository(PageText::class);

        $textToDisplay = $pageTextRepo->findOneBy(['page' => 'qui-sommes-nous'], [ 'id' => 'DESC']);

        // Création d'un texte vide
        $newPageText = new PageText();

        // Nouveau formulaire
        $form = $this->createForm(PageTextType::class, $newPageText);

        // Remplissage du traitement du form
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()){

            $newPageText->setPage('qui-sommes-nous')
            ->setContent($form->get('content')->getData());

            // On supprime les éventuels anciens contenus ayant le même nom de page
            $textsToDelete = $pageTextRepo->findBy(['page' => 'qui-sommes-nous']);


            // Récupération de l'entitymanager
            $em = $this->getDoctrine()->getManager();
            $em->persist($newPageText);
            foreach($textsToDelete as $textToDelete){
                $em->remove($textToDelete);
            }
            $em->flush();

            // Message de succès
            $this->addFlash('success', 'Contenu de la page mis à jour !');

            // Redirection
            return $this->redirectToRoute('qui-sommes-nous');
        }

        return $this->render('main/quiSommesNous.html.twig', [
            'form' => $form->createView(),
            'text' => $textToDisplay
        ]);
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
    public function documentation(Request $request, PaginatorInterface $paginator)
    {
        $newDocument = new Document();

        $form = $this->createForm(DocumentType::class, $newDocument);

        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();

        if($form->isSubmitted() && $form->isValid()){

            $file = $form->get('file')->getData();

            function slugify($text){
            $text = preg_replace('~[^\pL\d]+~u', '-', $text);
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
            $text = preg_replace('~[^-\w]+~', '', $text);
            $text = trim($text, '-');
            $text = preg_replace('~-+~', '-', $text);
            $text = strtolower($text);
            return $text;
            }

            $extensionName = $file->guessExtension();

            $slugName = slugify($form->get('name')->getData()) . '.' . $extensionName;

            $fileSize = $file->getClientSize();

            /** Fonction pour convertir la taille des fichiers de documentation */
            function convertOctet($val , $type_val , $type_wanted)
            {
                $tab_val = array("o", "ko", "Mo");
                if (!(in_array($type_val, $tab_val) && in_array($type_wanted, $tab_val)))
                return 0;
                $tab = array_flip($tab_val);
                $diff = $tab[$type_val] - $tab[$type_wanted];
                if ($diff > 0)
                return ($val * pow(1024, $diff));
                if ($diff < 0)
                return ($val / pow(1024, -$diff));
                return ($val);
            }

            $fileSizeAndValue = round(convertOctet($fileSize, 'o', 'Mo'), 2) . 'Mo';

            $file->move(
                $this->getParameter('app.document.directory'),
                $slugName
            );

            $newDocument
                ->setExtension($extensionName)
                ->setSize($fileSizeAndValue)
            ;

            $em->persist($newDocument);
            $em->flush();

            $this->addFlash('success', 'Document ajouté avec succès !');

            return $this->redirectToRoute('documentation');
        }

        $requestedPage = $request->query->getInt('page', 1);
        if($requestedPage < 1){
            throw new NotFoundHttpException();
        }

        $query = $em->createQuery('SELECT d FROM App\Entity\Document d ORDER BY d.name');

        $pageDocuments = $paginator->paginate(
            $query, $requestedPage, 20
        );

        return $this->render('main/documentation.html.twig', [
            'documents' => $pageDocuments,
            'form' => $form->createView()
        ]);
    }

    /**
     * Page de galerie
     *
     * @Route("/galerie/", name="galerie")
     */
    public function galerie(Request $request, PaginatorInterface $paginator)
    {
        // On récupère dans l'url la données GET page (si elle n'existe pas, la valeur retournée par défaut sera la page 1)
        $requestedPage = $request->query->getInt('page', 1);

        // Si le numéro de page demandé dans l'url est inférieur à 1, erreur 404
        if($requestedPage < 1){
            throw new NotFoundHttpException();
        }

        // Récupération du manager des entités
        $em = $this->getDoctrine()->getManager();

        // Récupération du repository des articles
        $articleRepo = $this->getDoctrine()->getRepository(Article::class);

        // On demande au repository de nous donner les articles ayant une galerie
        $allArticlesWithGallery = $articleRepo->findAllWithGallery();

        // On demande au GalleryRepo de récupérer toutes les photos au cas où il n'y en ait aucun, pour afficher un message
        $galleryRepo = $this->getDoctrine()->getRepository(Gallery::class);
        $galleryIsNull = $galleryRepo->findAll();

        //On stocke dans $pageArticles les 10 articles de la page demandée dans l'URL
        $pageArticles = $paginator->paginate(
            $allArticlesWithGallery,     // selection des articles
            $requestedPage,     // Numéro de la page dont on veux les articles
            5      // Nombre d'articles par page
        );

        return $this->render('main/galerie.html.twig', [
            'articles' => $pageArticles,
            'galleryIsNull' => $galleryIsNull
        ]);
    }

    /**
     * Page de mentions légales
     *
     * @Route("/mentions/", name="mentions")
     */
    public function mentions()
    {
        return $this->render('main/mentions.html.twig');
    }

    /**
     * Page admin d'accès aux inscrits et bénévoles
     *
     * @Route("/admin/", name="admin")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function admin()
    {
        $userRepo = $this->getDoctrine()->getRepository(User::class);
        $newUsers = $userRepo->findByValidation();


        return $this->render('main/admin.html.twig', [
            'newUsers' => $newUsers
        ]);
    }

    /**
     * Fonction qui accepte un user
     *
     * @Route("/admin/accepter/{id}/", name="admin-accept")
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @param \App\Entity\User $user
     * @param \Doctrine\ORM\EntityManagerInterface $manager
     */
    public function acceptUser(User $user, EntityManagerInterface $manager)
    {
        $user->setIsVerified(1);
        $manager->persist($user);
        $manager->flush();

        // ENVOYER MAIL

        return $this->redirectToRoute('admin');
    }

    /**
     * Fonction qui refuse un user
     *
     * @Route("/admin/refuser/{id}/", name="admin-refuse")
     * @Security("is_granted('ROLE_ADMIN')")
     *
     * @param \App\Entity\User $user
     * @param \Doctrine\ORM\EntityManagerInterface $manager
     */
    public function refuseUser(User $user, EntityManagerInterface $manager)
    {
        $manager->remove($user);
        $manager->flush();

        return $this->redirectToRoute('admin');
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
                $participationsCounter = $article->getParticipationsCounter();
                $article->setParticipationsCounter(--$participationsCounter);

                // On enlève cette participation dans la BDD
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
                $participationsCounter = $article->getParticipationsCounter();
                $article->setParticipationsCounter(++$participationsCounter);

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

    /**
     * Fonction qui permet de supprimer des images de la galerie
     *
     * @Route("/supprimer-image/{id}", name="delete_gallery_image", methods={"DELETE"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteGalleryImage(Gallery $galleryImage, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        // Vérification de la validité du token
        if($this->isCsrfTokenValid('delete'.$galleryImage->getId(), $data['_token'])){
            // On récupère le nom de l'image
            $name = $galleryImage->getName();

            // On supprime le fichier
            unlink($this->getParameter('app.article.photo.directory').'/'.$name);

            $em = $this->getDoctrine()->getManager();

            $em->remove($galleryImage);

            $em->flush();

            // On répond en JSON
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Token invalide !'], 400);
        }
    }

    /**
     * Fonction qui permet de supprimer des docs de la documentation
     *
     * @Route("/supprimer-doc/{id}", name="delete_doc", methods={"DELETE"})
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function deleteDoc(Document $document, Request $request)
    {
        $data = json_decode($request->getContent(), true);

        // Vérification de la validité du token
        if($this->isCsrfTokenValid('delete'.$document->getId(), $data['_token'])){
            // On récupère le nom de l'image
            $name = $document->getSlug().'.'. $document->getExtension();

            // On supprime le fichier
            unlink($this->getParameter('app.document.directory').'/'.$name);

            $em = $this->getDoctrine()->getManager();

            $em->remove($document);

            $em->flush();

            // On répond en JSON
            return new JsonResponse(['success' => 1]);
        } else {
            return new JsonResponse(['error' => 'Token invalide !'], 400);
        }
    }
}
