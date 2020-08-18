// Système de likes

// Fonction permettant de gérer les likes
function onClickBtnLike(event){

    // On empêche le chargement de la page de like, créée dans le ArticleController
    event.preventDefault();

    // On récupère l'url
    let url = this.href;

    // On sélectionne le span contenant le nombre de likes...
    let spanCount = this.querySelector(".p-count");
    // ... ainsi que l'icône du pouce en l'air
    let button = this.querySelector(".participation");

    // Grâce au bundle axios, installé en CDN dans ArticleList.hmtl.twig...
    axios.get(url).then(function(response){

        location.reload();

    }).catch(function(error){

        // Si l'utilisateur n'est pas connecté...
        if(error.response.status === 403){
            // ... on affiche une erreur dans une fenêtre d'alerte
            window.alert("Il faut être connecté pour liker un article !");
        // ...sinon, pour n'importe quelle autre erreur, on affiche un autre alert
        } else {
            window.alert("Une erreur s'est produite, veuillez réessayer plus tard.");
        }
    });
}

// On sélectionne les boutons de like et on leur ajoute un écouteur d'évènement au clic, en appelant la fonction qu'on a créé ci-dessus
document.querySelectorAll(".participation-link").forEach(function(link){
    link.addEventListener('click', onClickBtnLike);
});
