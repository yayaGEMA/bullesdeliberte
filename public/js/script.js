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



////// Système de suppression de photos

window.onload = () => {
    // Gestion des boutons "X"
    let links = document.querySelectorAll("[data-delete]");

    // On boucle sur links
    for(link of links){
        // On écoute le clic
        link.addEventListener('click', function(e){
            // On empêche la navigation
            e.preventDefault();

            // On demande confirmation
            if(confirm("Voulez-vous supprimer cette image ?")){
                // On envoie une requête AJAX vers le href du lien avec la méthode DELETE
                fetch(this.getAttribute("href"), {
                    method: "DELETE",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify({"_token": this.dataset.token})
                }).then(
                    // On récupère la réponse en JSON
                    response => response.json()
                ).then(data => {
                    if(data.success){
                        this.parentElement.parentElement.remove();
                    } else {
                        alert(data.error);
                    }
                }).catch(e => alert(e))
            }
        });
    }
}


////// Système de carousel et overlay

$('.gallery-img').click(function(){

    displayImage( $(this).data('image') );
});

// Création d'une fonction qui affiche une image dans un overlay avec un croix de fermeture
function displayImage(imageName){

    // Création d'une div servant d'overlay
    let overlay = $('<div></div>');
    overlay.addClass('overlay');
    $('body').prepend( overlay );

    // Création de l'image
    let image = $('<img alt="">');
    image.attr('src', "/images/articles/" + imageName);
    image.css("max-width", "90vw");
    image.css("max-height", "90vh");
    overlay.append( image );

    // Création du bouton de fermeture
    let closeButton = $('<div></div>');
    closeButton.text('X');
    closeButton.addClass('close');
    overlay.append(closeButton);

    // Application d'un écouteur d'évènement "click" sur le bouton de fermeture
    closeButton.click(function(){

        // Appel de la fonction permettant de supprimer l'overlay
        removeImage();
    });

    $(document).keyup(function(e) {
        if (e.key === "Escape"){
           removeImage();
       }
   });

}

// Suppression de l'overlay contenant l'image et la croix de fermeture
function removeImage(){
    $('.overlay').remove();
}