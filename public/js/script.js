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

    displayImage($(this).data('image'), $(this).data('artnumber'), $(this).data('number') );
});

// Création d'une fonction qui affiche une image dans un overlay avec un croix de fermeture
function displayImage(imageName, artnumber, arrayNumber){

    // On récupère l'id de l'élément cliqué
    let idName = `#art-${artnumber}-img-${arrayNumber}`;

    // On récupère le nom de chaque img dans la galerie de l'article dans un array
    let imgArray = [];
    $(idName).siblings().add(idName).each(function(){
        imgArray.push($(this).data('image'));
    });

    // Création d'une div servant d'overlay
    let overlay = $('<div></div>');
    overlay.addClass('overlay');
    $('body').prepend( overlay );

    // Création des div de carousel
    let carouselSlide = $('<div id="carouselExample" class="carousel slide w-50" data-ride="carousel"><div class="carousel-inner"></div><a class="carousel-control-prev" href="#carouselExample" role="button" data-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="sr-only">Previous</span></a><a class="carousel-control-next" href="#carouselExample" role="button" data-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="sr-only">Next</span></a></div>');
    overlay.append(carouselSlide);

    $.each(imgArray, function(i, l ){
        // Création d'une div carousel-item autant qu'il y a d'images dans l'article
        let carouselItem = $('<div></div>');
        carouselItem.addClass('carousel-item item-' + i);
        $('.carousel-inner').append(carouselItem);

        // Création de l'image
        let image = $('<img alt="carousel-'+ i + '">');
        image.attr('src', `/images/articles/${l}`);
        image.addClass('d-block w-100');
        $('.item-' + i).append( image );
    });

    // Ajout de la class active à l'image cliquée
    let itemActive = '.item-'+(arrayNumber-1);
    $(itemActive).addClass('active');

    // Création du bouton de fermeture
    let closeButton = $('<div></div>');
    closeButton.text('X');
    closeButton.addClass('close');
    overlay.append(closeButton);

    // Application d'un écouteur d'évènement "click" sur le bouton de fermeture
    $(".close").click(function(){

        // Appel de la fonction permettant de supprimer l'overlay
        removeImage();
    });

    // On ajoute la fonction sur la touche ECHAP également
    $(document).keyup(function(e) {
        if (e.key === 37){
           removeImage();
       }
    });

}

// Suppression de l'overlay contenant l'image et la croix de fermeture
function removeImage(){
    $('.overlay').remove();
}